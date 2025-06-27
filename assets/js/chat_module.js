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
            if (chatHeaderPic) chatHeaderPic.src = receiverPic;
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
    const messageOptionsModalElement = document.getElementById('messageOptionsModal');
    const deleteConfirmationModalElement = document.getElementById('deleteConfirmationModal');

    if (!messageOptionsModalElement || !deleteConfirmationModalElement) return;

    const messageOptionsModal = new bootstrap.Modal(messageOptionsModalElement);
    const deleteConfirmationModal = new bootstrap.Modal(deleteConfirmationModalElement);
    let currentMessageId = null;

    // Delegate event listener to the messages container
    const messagesContainer = document.getElementById('messages');
    if (!messagesContainer.dataset.modalListenerAttached) {
        messagesContainer.addEventListener('click', function (event) {
            const button = event.target.closest('.message-options-btn');
            if (button) {
                currentMessageId = button.dataset.id;
                const isSender = button.dataset.isSender === '1';
                const deleteOption = messageOptionsModalElement.querySelector('#modal-option-delete');
                if (deleteOption) {
                    deleteOption.style.display = isSender ? 'block' : 'none';
                }
                messageOptionsModal.show();
            }
        });
        messagesContainer.dataset.modalListenerAttached = 'true';
    }


    // Handle click on the modal's delete option
    const modalDeleteOption = messageOptionsModalElement.querySelector('#modal-option-delete');
    if (modalDeleteOption) {
        modalDeleteOption.addEventListener('click', function (e) {
            e.preventDefault();
            messageOptionsModal.hide();
            // Now, show the confirmation modal
            deleteConfirmationModal.show();
        });
    }

    // Handle the final confirmation from the new modal
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function () {
            if (currentMessageId) {
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
            deleteConfirmationModal.hide();
            currentMessageId = null; // Reset after deletion
        });
    }
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

        if (message === '' && !selectedAttachmentFile) {
            alert("Please enter a message or attach a file.");
            return;
        }
        if (!receiverId) {
            alert("Please select a user to chat with.");
            return;
        }

        const formData = new FormData();
        formData.append('receiver_id', receiverId);
        formData.append('message', message);
        if (selectedAttachmentFile) {
            formData.append('attachment', selectedAttachmentFile);
        }
        if (replyingToMessageId) {
            formData.append('reply_to_message_id', replyingToMessageId);
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