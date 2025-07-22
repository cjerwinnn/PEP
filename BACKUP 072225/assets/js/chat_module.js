let inboxRefreshInterval = null;

function Chat_FetchEmployeeList(targetId, url) {
    const container = document.getElementById(targetId);

    if (!container) return;

    fetch(url)
        .then(response => response.text())
        .then(data => {
            container.innerHTML = data;

            document.querySelectorAll(`#${targetId} .employee-item`).forEach(item => {
                item.addEventListener('click', function () {
                    const employeeId = this.getAttribute('data-id');
                    const employeeName = this.textContent;

                    // Optional: Load chat conversation
                    document.getElementById('chat-box').innerHTML =
                        `<p class="text-muted">Conversation with ${employeeName} loaded.</p>`;
                });
            });

        })
        .catch(error => {
            container.innerHTML = '<p class="text-danger p-3">Failed to load employees.</p>';
            console.error('Error loading employee list:', error);
        });
}

function fetchEmployees(searchTerm = '') {
    const url = `chat_module/fetch_employees.php?search=${encodeURIComponent(searchTerm)}`;
    fetch(url, {
        method: 'GET'
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById('user-list').innerHTML = data;
            attachUserClickHandlers();
        })
        .catch(error => {
            document.getElementById('user-list').innerHTML = '<p>Error loading employees.</p>';
            console.error('Fetch error:', error);
        });
}

function fetchInbox() {
    fetch('chat_module/fetch_inbox.php', {
        method: 'GET'
    })
        .then(response => response.text())
        .then(data => {
            const inboxList = document.getElementById('inbox-list');
            if (inboxList) {
                inboxList.innerHTML = data;
                attachUserClickHandlers();
            }

        })
        .catch(error => {
            const inboxList = document.getElementById('inbox-list');
            if (inboxList) {
                inboxList.innerHTML = '<p>Error loading inbox.</p>';
            }
            console.error('Fetch error:', error);
        });
}

let refreshIntervalId = null;
let lastMessageId = 0;
let selectedAttachmentFile = null; // Global variable to hold the selected file

function attachUserClickHandlers() {
    const users = document.querySelectorAll('.employee-item');
    users.forEach(user => {
        user.addEventListener('click', function () {
            const receiverId = this.dataset.id;
            const receiverName = this.dataset.name;
            const receiverPic = this.dataset.pic;

            document.getElementById('receiver').value = receiverId;

            // Update chat header
            const chatHeader = document.getElementById('chat-header');
            const chatHeaderPic = document.getElementById('chat-header-pic');
            const chatHeaderName = document.getElementById('chat-header-name');

            if (chatHeader) chatHeader.style.display = 'flex';
            if (chatHeaderPic) {
                chatHeaderPic.src = receiverPic;
                chatHeaderPic.title = receiverName; // Add this line
            }
            if (chatHeaderName) chatHeaderName.innerHTML = `[${receiverId}] ${receiverName}`;


            users.forEach(u => u.classList.remove('selected'));
            this.classList.add('selected');

            lastMessageId = 0;
            loadChatMessages(receiverId, true);

            if (refreshIntervalId) {
                clearInterval(refreshIntervalId);
            }

            refreshIntervalId = setInterval(() => {
                loadChatMessages(receiverId, false);
            }, 1000);
        });
    });
}


function loadChatMessages(receiverId, scroll = false) {
    const url = `chat_module/fetch_messages.php?receiver_id=${encodeURIComponent(receiverId)}`;

    fetch(url)
        .then(response => response.text())
        .then(data => {
            const messagesDiv = document.getElementById('messages');
            messagesDiv.innerHTML = data;

            const allMessages = messagesDiv.querySelectorAll('[data-message-id]');
            if (allMessages.length > 0) {
                lastMessageId = parseInt(allMessages[allMessages.length - 1].getAttribute('data-message-id'));
            } else {
                lastMessageId = 0;
            }

            if (scroll) {
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }

            setupMessageOptionsModal();
            setupReactionPopup();
            attachAttachmentViewers();

        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

function setupMessageOptionsModal() {
    const modalElement = document.getElementById('messageOptionsModal');
    if (!modalElement) return;

    const messageOptionsModal = new bootstrap.Modal(modalElement);
    let currentMessageId = null;

    const messagesContainer = document.getElementById('messages');
    if (!messagesContainer.dataset.modalListenerAttached) {
        messagesContainer.addEventListener('click', function (event) {
            const button = event.target.closest('.message-options-btn');
            if (button) {
                currentMessageId = button.dataset.id;
                modalElement.dataset.messageId = currentMessageId; // Store messageId on the modal element
                const isSender = button.dataset.isSender === '1';
                const deleteOption = modalElement.querySelector('#modal-option-delete');
                deleteOption.style.display = isSender ? 'block' : 'none';
                messageOptionsModal.show();
            }
        });
        messagesContainer.dataset.modalListenerAttached = 'true';
    }


    // --- Action Handlers for Modal Buttons ---

    // Reply Action
    modalElement.querySelector('#modal-option-reply').addEventListener('click', function (e) {
        e.preventDefault();
        messageOptionsModal.hide();
        const messageElement = document.querySelector(`[data-message-id='${currentMessageId}'] .fw-normal`);
        const messageBubble = document.querySelector(`[data-message-id='${currentMessageId}']`);

        if (messageElement && messageBubble) {
            const isSender = messageBubble.classList.contains('message-sent');
            const replyToName = isSender ? 'You' : document.getElementById('chat-header-name').innerText.split('] ')[1];

            document.getElementById('reply-to-name').innerText = replyToName;
            document.getElementById('reply-to-text').innerText = messageElement.innerText;
            document.getElementById('reply-to-container').style.display = 'block';
            replyingToMessageId = currentMessageId;
            document.getElementById('message').focus();
        }
    });

    // Cancel Reply Action
    const cancelReplyButton = document.getElementById('cancel-reply');
    if (cancelReplyButton) {
        cancelReplyButton.addEventListener('click', function () {
            document.getElementById('reply-to-container').style.display = 'none';
            replyingToMessageId = null;
        });
    }

    // Delete Action
    modalElement.querySelector('#modal-option-delete').addEventListener('click', function (e) {
        e.preventDefault();
        messageOptionsModal.hide();
        if (currentMessageId && confirm('Are you sure you want to delete this message?')) {
            const receiverId = document.getElementById('receiver').value;
            const formData = new URLSearchParams();
            formData.append('id', currentMessageId);

            fetch('chat_module/delete_message.php', {
                method: 'POST',
                body: formData,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
                .then(response => response.text())
                .then(result => {
                    if (result.includes('Message deleted successfully.')) {
                        loadChatMessages(receiverId, false);
                    } else {
                        alert('Error deleting message: ' + result);
                    }
                })
                .catch(error => console.error('Delete message error:', error));
        }
    });
}

function setupChatFormSubmit() {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message');
    const attachmentInput = document.getElementById('attachment-input');
    const attachmentPreview = document.getElementById('attachment-preview');
    let replyingToMessageId = null;
    let selectedAttachmentFile = null;

    // Listen to reply action
    document.getElementById('modal-option-reply').addEventListener('click', function () {
        const messageOptionsModal = bootstrap.Modal.getInstance(document.getElementById('messageOptionsModal'));
        if (messageOptionsModal) {
            messageOptionsModal.hide();
        }
        const messageId = document.querySelector('#messageOptionsModal').dataset.messageId; // Correctly get the messageId
        const messageElement = document.querySelector(`[data-message-id='${messageId}'] .fw-normal`);
        const messageBubble = document.querySelector(`[data-message-id='${messageId}']`);

        if (messageElement && messageBubble) {
            const isSender = messageBubble.classList.contains('message-sent');
            const replyToName = isSender ? 'You' : document.getElementById('chat-header-name').innerText.split('] ')[1];

            document.getElementById('reply-to-name').innerText = replyToName;
            document.getElementById('reply-to-text').innerText = messageElement.innerText;
            document.getElementById('reply-to-container').style.display = 'block';
            replyingToMessageId = messageId;
            document.getElementById('message').focus();
        }
    });

    document.getElementById('cancel-reply').addEventListener('click', function () {
        replyingToMessageId = null;
        document.getElementById('reply-to-container').style.display = 'none';
    });


    attachmentInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            selectedAttachmentFile = this.files[0];
            attachmentPreview.innerHTML = '';

            const file = selectedAttachmentFile;
            const reader = new FileReader();
            const previewItem = document.createElement('div');
            previewItem.classList.add('attachment-preview-item');

            let previewContent = '';
            if (file.type.startsWith('image/')) {
                reader.onload = function (e) {
                    previewContent = `<img src="${e.target.result}" class="attachment-icon" alt="Preview"> <span>${file.name}</span> <span class="remove-attachment" data-type="clear">&times;</span>`;
                    previewItem.innerHTML = previewContent;
                    previewItem.querySelector('.remove-attachment').addEventListener('click', function () {
                        selectedAttachmentFile = null;
                        attachmentInput.value = '';
                        attachmentPreview.innerHTML = '';
                    });
                };
                reader.readAsDataURL(file);
            } else {
                const iconSrc = file.type === 'application/pdf' ? 'assets/icons/pdf-icon.png' : 'assets/icons/file-icon.png';
                const style = file.type === 'application/pdf' ? 'style="width: 30px; height: auto;"' : '';
                previewContent = `<img src="${iconSrc}" class="attachment-icon" ${style} alt="File Icon"> <span>${file.name}</span> <span class="remove-attachment" data-type="clear">&times;</span>`;
                previewItem.innerHTML = previewContent;
                previewItem.querySelector('.remove-attachment').addEventListener('click', function () {
                    selectedAttachmentFile = null;
                    attachmentInput.value = '';
                    attachmentPreview.innerHTML = '';
                });
            }
            attachmentPreview.appendChild(previewItem);
        } else {
            selectedAttachmentFile = null;
            attachmentPreview.innerHTML = '';
        }
    });


    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const senderId = document.getElementById('sender').value;
        const receiverId = document.getElementById('receiver').value;
        const message = messageInput.value.trim();

        if (message === '' && !selectedAttachmentFile && !replyingToMessageId) {
            alert("Please enter a message, attach a file, or reply to a message.");
            return;
        }
        if (!receiverId) {
            alert("Please select a user to chat with.");
            return;
        }

        const formData = new FormData();
        formData.append('receiver_id', receiverId);
        formData.append('message', message);

        if (replyingToMessageId) {
            formData.append('reply_to_message_id', replyingToMessageId);
        }

        // Also include the attachment if one is selected
        if (selectedAttachmentFile) {
            formData.append('attachment', selectedAttachmentFile, selectedAttachmentFile.name);
        }

        fetch('chat_module/send_message.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === 'Message sent') {
                    messageInput.value = '';
                    selectedAttachmentFile = null;
                    attachmentInput.value = '';
                    attachmentPreview.innerHTML = '';

                    // Reset reply state
                    replyingToMessageId = null;
                    document.getElementById('reply-to-container').style.display = 'none';

                    lastMessageId = 0;
                    loadChatMessages(receiverId, true);
                } else {
                    alert('Error sending message: ' + result);
                }
            })
            .catch(error => {
                console.error('Send message error:', error);
                alert('Error sending message');
            });
    });

    messageInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            if (event.shiftKey) {
            } else {
                event.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        }
    });
}

function attachMessageDeleteHandlers() {
    document.querySelectorAll('.message-delete-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const messageId = this.getAttribute('data-id');
            const receiverId = document.getElementById('receiver').value;

            if (confirm('Are you sure you want to delete this message?')) {
                const formData = new URLSearchParams();
                formData.append('id', messageId);

                fetch('chat_module/delete_message.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => { throw new Error(text) });
                        }
                        return response.text();
                    })
                    .then(result => {
                        if (result.includes('Message deleted successfully.')) {
                            lastMessageId = 0;
                            loadChatMessages(receiverId, false);
                        } else {
                            alert('Error: ' + result);
                        }
                    })
                    .catch(error => {
                        console.error('Delete message error:', error);
                        alert('Failed to delete message: ' + error.message);
                    });
            }
        });
    });
}

function attachAttachmentViewers() {
    const viewerModal = document.getElementById('attachment-viewer-modal');
    if (!viewerModal) return;

    const viewerImage = document.getElementById('viewer-image');
    const viewerPdf = document.getElementById('viewer-pdf');
    const viewerFilename = document.getElementById('viewer-filename');
    const closeBtn = viewerModal.querySelector('.attachment-viewer-close');

    closeBtn.onclick = function () {
        viewerModal.style.display = "none";
        viewerImage.src = '';
        viewerImage.style.display = 'none';
        viewerPdf.src = '';
        viewerPdf.style.display = 'none';
        viewerFilename.textContent = '';
    };

    viewerModal.addEventListener('click', function (event) {
        if (event.target === viewerModal) {
            closeBtn.click();
        }
    });

    document.querySelectorAll('.message-attachment').forEach(attachmentDiv => {
        attachmentDiv.addEventListener('click', function (event) {
            event.preventDefault();
            const src = this.getAttribute('data-src');
            const type = this.getAttribute('data-type');
            const filename = this.getAttribute('data-filename');

            if (!src || !type) return;

            viewerFilename.textContent = filename;

            if (type.startsWith('image/')) {
                viewerImage.src = src;
                viewerImage.style.display = 'block';
                viewerPdf.style.display = 'none';
                viewerModal.style.display = "block";
            } else if (type === 'application/pdf') {
                viewerPdf.src = src;
                viewerPdf.style.display = 'block';
                viewerImage.style.display = 'none';
                viewerModal.style.display = "block";
            } else {
                window.open(src, '_blank');
            }
        });
    });
}

function setupTabSwitching() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active-tab'));
            tabContents.forEach(c => c.classList.remove('active'));

            btn.classList.add('active-tab');
            const targetId = btn.getAttribute('data-target');
            const target = document.getElementById(targetId);
            if (target) {
                target.classList.add('active');
            }
        });
    });
}

function setupUserSearch() {
    const searchInput = document.getElementById('userSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            fetchEmployees(this.value);
        });
    }
}


function initializeTabSwitching() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active-tab'));
            tabContents.forEach(tab => tab.classList.remove('active'));

            btn.classList.add('active-tab');
            const targetTab = document.getElementById(btn.dataset.target);
            if (targetTab) targetTab.classList.add('active');
        });
    });

    const mobileSelect = document.getElementById('mobileTabSelect');
    if (mobileSelect) {
        mobileSelect.addEventListener('change', function () {
            const selectedTab = this.value;
            tabContents.forEach(tab => tab.classList.remove('mobile-active'));
            const activeTab = document.getElementById(selectedTab);
            if (activeTab) activeTab.classList.add('mobile-active');
        });

        const initialTab = mobileSelect.value;
        const initialContent = document.getElementById(initialTab);
        if (initialContent) initialContent.classList.add('mobile-active');
    }
}

function setupReactionPopup() {
    const popup = document.getElementById('reaction-popup');
    if (!popup) {
        console.error("Reaction popup element with id 'reaction-popup' not found in the DOM.");
        return;
    }

    let currentMessageId = null;

    document.querySelectorAll('.reaction-popup-btn').forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
    });

    document.querySelectorAll('.reaction-popup-btn').forEach(btn => {
        btn.addEventListener('click', (event) => {
            event.stopPropagation();
            currentMessageId = btn.getAttribute('data-id');

            const rect = btn.getBoundingClientRect();
            popup.style.top = (window.scrollY + rect.top - popup.offsetHeight - 5) + 'px';
            popup.style.left = (window.scrollX + rect.left) + 'px';
            popup.style.display = 'block';
        });
    });

    const reactionMap = {
        '1': '👍', '2': '❤️', '3': '😂', '4': '😮', '5': '😢', '6': '🔥', '7': '🎉', '8': '😡',
    };

    popup.querySelectorAll('.reaction-choice').forEach(span => {
        span.addEventListener('click', () => {
            if (!currentMessageId) return;

            const selectedReactionId = span.getAttribute('data-id');

            fetch('chat_module/save-reaction.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${encodeURIComponent(currentMessageId)}&reaction=${encodeURIComponent(selectedReactionId)}`
            })
                .then(res => res.text())
                .then(response => {
                    if (response.trim() === 'OK') {
                        const receiverId = document.getElementById('receiver').value;
                        if (receiverId) {
                            lastMessageId = 0;
                            loadChatMessages(receiverId, false);
                        }
                    } else {
                        alert('Failed to save reaction: ' + response);
                    }
                })
                .catch(error => {
                    console.error('Error saving reaction:', error);
                    alert('Error saving reaction.');
                });

            popup.style.display = 'none';
            currentMessageId = null;
        });
    });

    document.addEventListener('click', (e) => {
        if (!popup.contains(e.target) && !e.target.closest('.reaction-popup-btn')) {
            popup.style.display = 'none';
            currentMessageId = null;
        }
    });
}

function updateUserStatuses() {
    const userItems = document.querySelectorAll('.employee-item');
    if (userItems.length === 0) return;

    const userIds = [...new Set(Array.from(userItems).map(item => item.dataset.id))];

    if (userIds.length === 0) return;

    const formData = new FormData();
    formData.append('user_ids', JSON.stringify(userIds));

    fetch('chat_module/fetch_user_status.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(statuses => {
            for (const userId in statuses) {
                const indicators = document.querySelectorAll(`#status-${userId}, #status-inbox-${userId}`);
                indicators.forEach(indicator => {
                    if (statuses[userId]) {
                        indicator.classList.remove('status-offline');
                        indicator.classList.add('status-online');
                    } else {
                        indicator.classList.remove('status-online');
                        indicator.classList.add('status-offline');
                    }
                });
            }
        })
        .catch(error => console.error('Error fetching user statuses:', error));
}

function openCreateGroupModal() {
    const groupNameInput = $('#create_group_name');
    const searchInput = $('#employee-search-input');
    const searchResults = $('#search-results-list');
    const selectedMembersList = $('#selected-members-list');
    let selectedMembers = {};

    // --- Validation ---
    if (groupNameInput.length === 0) {
        Swal.fire('Error', 'The group name input field could not be found. Please check the HTML of your modal.', 'error');
        return;
    }

    // --- Employee Search ---
    searchInput.on('keyup', function () {
        const query = $(this).val();
        if (query.length > 2) { // Start searching after 2 characters
            $.ajax({
                url: 'chat_module/fetch_employees.php',
                type: 'GET',
                data: {
                    search: query
                },
                success: function (data) {
                    searchResults.html(data).show();
                }
            });
        } else {
            searchResults.hide();
        }
    });

    // --- Add Member to Selection ---
    $(document).on('click', '#search-results-list .employee-item', function () {
        const memberId = $(this).data('id');
        const memberName = $(this).data('name');
        const memberPic = $(this).data('pic');

        if (!selectedMembers[memberId]) {
            selectedMembers[memberId] = {
                name: memberName,
                pic: memberPic
            };
            const memberElement = `
                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${memberId}">
                    <div>
                        <img src="${memberPic}" class="profile-pic me-2" alt="Profile">
                        ${memberName}
                    </div>
                    <button class="btn btn-sm btn-outline-danger remove-member-btn" data-id="${memberId}">&times;</button>
                </li>`;
            selectedMembersList.append(memberElement);
        }
        searchInput.val('');
        searchResults.hide();
    });

    // --- Remove Member from Selection ---
    $(document).on('click', '.remove-member-btn', function () {
        const memberId = $(this).data('id');
        delete selectedMembers[memberId];
        $(this).closest('.list-group-item').remove();
    });

    const groupName = groupNameInput.val().trim();
    if (groupName === '') {
        Swal.fire('Validation Error', 'Please enter a group name.', 'error');
        return;
    }

    const members = Object.keys(selectedMembers);
    if (members.length === 0) {
        Swal.fire('Validation Error', 'Please add at least one member to the group.', 'error');
        return;
    }

    const createBtn = $(this);
    createBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...');

    // --- AJAX Request to Create Group ---
    $.ajax({
        url: 'chat_module/create_group.php',
        type: 'POST',
        data: {
            group_name: groupName,
            group_members: members // Pass the array of member IDs
        },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                Swal.fire('Success!', response.message, 'success');

                // Close the modal and reset fields
                $('#createGroupModal').modal('hide');
                groupNameInput.val('');
                selectedMembersList.empty();
                searchInput.val('');

                // Refresh the group list
                fetchGroups();
            } else {
                Swal.fire('Error', response.message || 'Could not create group.', 'error');
            }
        },
        error: function (xhr) {
            console.error("AJAX Error:", xhr.responseText);
            Swal.fire('Error', 'An unexpected server error occurred. Please try again.', 'error');
        },
        complete: function () {
            // Re-enable the button
            createBtn.prop('disabled', false).text('Create Group');
        }
    });
}


document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'submit-new-group-btn') {
        openCreateGroupModal();
    }
});

const createGroupForm = document.getElementById('create-group-form');
if (createGroupForm) {
    createGroupForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const groupName = document.getElementById('group-name-input').value.trim();
        const selectedMembers = document.querySelectorAll('#group-members-list .form-check-input:checked');
        const userIds = Array.from(selectedMembers).map(cb => cb.value);

        if (!groupName) {
            alert('Please provide a group name.');
            return;
        }
        if (userIds.length === 0) {
            alert('Please select at least one member.');
            return;
        }

        const formData = new FormData();
        formData.append('group_name', groupName);
        formData.append('user_ids', JSON.stringify(userIds));

        fetch('chat_module/create_group.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('createGroupModal')).hide();
                    fetchGroups(); // Refresh group list
                } else {
                    alert('Error: ' + (data.message || 'An unknown error occurred.'));
                }
            })
            .catch(error => {
                console.error('Error creating group:', error);
                alert('A critical error occurred. Check the console for details.');
            });
    });
}


$(document).ready(function () {
    let selectedMembers = {};

    // Show all employees when modal opens
    $('#createGroupModal').on('shown.bs.modal', function () {
        // Fetch all employees (no search term)
        $.ajax({
            url: 'chat_module/fetch_employees.php',
            type: 'GET',
            data: { search: '' },
            success: function (data) {
                $('#search-results-list').html(data).show();
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                $('#search-results-list').html('<div class="list-group-item">Error fetching employees.</div>').show();
            }
        });
    });

    // Handle employee search
    $('#employee-search-input').on('keyup', function () {
        const query = $(this).val();
        if (query.length > 2) {
            $.ajax({
                url: 'chat_module/fetch_employees.php',
                type: 'GET',
                data: {
                    search: query
                },
                success: function (data) {
                    $('#search-results-list').html(data).show();
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                    $('#search-results-list').html('<div class="list-group-item">Error fetching employees.</div>').show();
                }
            });
        } else {
            // Show all employees if input is cleared
            if (query.length === 0) {
                $.ajax({
                    url: 'chat_module/fetch_employees.php',
                    type: 'GET',
                    data: { search: '' },
                    success: function (data) {
                        $('#search-results-list').html(data).show();
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", error);
                        $('#search-results-list').html('<div class="list-group-item">Error fetching employees.</div>').show();
                    }
                });
            } else {
                $('#search-results-list').hide();
            }
        }
    });

    // Handle adding a member
    $(document).on('click', '#search-results-list .employee-item', function () {
        const memberId = $(this).data('id');
        const memberName = $(this).data('name');
        const memberPic = $(this).data('pic');

        if (!selectedMembers[memberId]) {
            selectedMembers[memberId] = {
                name: memberName,
                pic: memberPic
            };
            const memberElement = `
                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${memberId}">
                    <div>
                        <img src="${memberPic}" class="profile-pic me-2" alt="Profile">
                        ${memberName}
                    </div>
                    <button class="btn btn-sm btn-outline-danger remove-member-btn" data-id="${memberId}">&times;</button>
                </li>`;
            $('#selected-members-list').append(memberElement);
        }
        $('#employee-search-input').val('');
        $('#search-results-list').hide();
    });

    // Handle removing a member
    $(document).on('click', '.remove-member-btn', function () {
        const memberId = $(this).data('id');
        delete selectedMembers[memberId];
        $(this).closest('.list-group-item').remove();
    });

    // Handle group creation
    $('#submit-new-group-btn').on('click', function () {
        const groupName = $('#create_group_name').val().trim();
        const members = Object.keys(selectedMembers);

        if (groupName === '') {
            Swal.fire('Validation Error', 'Please enter a group name.', 'error');
            return;
        }

        if (members.length === 0) {
            Swal.fire('Validation Error', 'Please add at least one member to the group.', 'error');
            return;
        }

        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...');

        $.ajax({
            url: 'chat_module/create_group.php',
            type: 'POST',
            data: {
                group_name: groupName,
                group_members: members
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire('Success!', response.message, 'success');
                    $('#createGroupModal').modal('hide');
                    $('#create_group_name').val('');
                    $('#selected-members-list').empty();
                    selectedMembers = {};
                    fetchGroups();
                } else {
                    Swal.fire('Error', response.message || 'Could not create group.', 'error');
                }
            },
            error: function (xhr) {
                console.error("AJAX Error:", xhr.responseText);
                Swal.fire('Error', 'An unexpected server error occurred.', 'error');
            },
            complete: function () {
                $('#submit-new-group-btn').prop('disabled', false).text('Create Group');
            }
        });
    });
});

function fetchGroups() {
    $.ajax({
        url: 'chat_module/fetch_groups.php',
        type: 'GET',
        success: function (data) {
            // Assuming you have a container with this ID to display the list of groups
            $('#group-list-container').html(data);
        },
        error: function () {
            console.error('Failed to fetch groups.');
        }
    });
}