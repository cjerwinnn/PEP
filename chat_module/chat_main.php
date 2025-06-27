<?php
include '../config/connection.php';
session_start();
if (!isset($_SESSION['employeeid'])) {
    header("Location: index.php");
    exit();
}

$user_id = isset($_SESSION['employeeid']) ? $_SESSION['employeeid'] : '';
?>

<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background-color: #f9f9f9;
    }

    #chat-container {
        display: flex;
        width: 100%;
        height: 90vh;
    }

    .user-item {
        display: flex;
        align-items: center;
        cursor: pointer;
        padding: 10px 12px;
        border-radius: 8px;
        transition: background 0.2s;
    }

    .user-item:hover {
        background: #e9ecef;
    }

    .employee-item.selected {
        background-color: #d0ebff;
        font-weight: 600;
    }

    .profile-pic,
    .chat-profile-pic {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
    }

    #chat-box {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    #messages {
        flex: 1;
        padding: 16px;
        overflow-y: auto;
        background: #ffffff;
        scroll-behavior: smooth;
    }

    #chat-form {
        display: flex;
        padding: 10px;
        border-top: 1px solid #dee2e6;
        background-color: #f8f9fa;
        align-items: flex-end;
        /* Align items to the bottom, useful for textarea */
    }

    /* Modified CSS to include textarea */
    #chat-form input[type="text"],
    #chat-form textarea {
        /* Add textarea here */
        flex: 1;
        padding: 10px 12px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 14px;
        resize: none;
        /* Prevent manual resizing by user */
        min-height: 40px;
        /* Ensure a minimum height for the textarea */
        overflow-y: auto;
        /* Allow scrolling if content overflows */
        line-height: 1.4;
        /* Standard line height for better readability */
    }

    #chat-form button {
        padding: 10px 16px;
        margin-left: 10px;
        border-radius: 8px;
    }

    .chat-message {
        margin-bottom: 12px;
        display: flex;
        align-items: flex-end;
        max-width: 80%;
    }

    .message-sent {
        justify-content: flex-end;
        margin-left: auto;
    }

    .message-received {
        justify-content: flex-start;
        margin-right: auto;
    }

    .message-bubble {
        position: relative;
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.4;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        max-width: 100%;
        word-break: break-word;
    }

    /* Styles for the image attachment within a chat bubble */
    .message-attachment img {
        max-width: 100%;
        /* Ensures image is never wider than its container */
        max-height: 250px;
        /* Prevents very tall images from taking up too much space */
        height: auto;
        /* Maintains aspect ratio */
        border-radius: 8px;
        /* Optional: for rounded corners */
        cursor: pointer;
        /* Indicates it can be clicked */
    }

    /* Styles for the attachment preview area before sending */
    #attachment-preview {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .attachment-preview-item {
        display: flex;
        align-items: center;
        background-color: #e9ecef;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 13px;
    }

    /* Styles for the small image thumbnail in the preview */
    .attachment-preview-item .attachment-icon {
        max-width: 50px;
        /* Set a max width for the preview thumbnail */
        max-height: 50px;
        /* Set a max height for the preview thumbnail */
        object-fit: cover;
        border-radius: 4px;
        margin-right: 8px;
    }

    .remove-attachment {
        cursor: pointer;
        margin-left: 10px;
        font-weight: bold;
        padding: 0 4px;
    }


    .message-sent .message-bubble {
        background-color: #d1e7dd;
        color: #0f5132;
        text-align: right;
    }

    .message-received .message-bubble {
        background-color: #f8d7da;
        color: #842029;
        text-align: left;
    }

    .message-time {
        font-size: 11px;
        color: #6c757d;
        margin-top: 5px;
    }

    .reaction-display {
        position: absolute;
        bottom: -8px;
        right: -8px;
        font-size: 1.1rem;
        background-color: #ffffff;
        border-radius: 50%;
        padding: 2px 4px;
        box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
    }

    .tab-btn {
        flex: 1;
        background: #f1f1f1;
        border: none;
        padding: 10px;
        font-weight: bold;
        cursor: pointer;
        text-align: center;
    }

    .tab-btn.active-tab {
        background: #ffffff;
        border-bottom: 2px solid #0d6efd;
        color: #0d6efd;
    }

    .tab-content {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .user-item .btn {
        background: none;
        border: none;
        font-size: 18px;
        color: #333;
    }

    .user-item .btn:focus {
        box-shadow: none;
    }

    .chat-date {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 8px auto;
        text-align: center;
    }

    .modal-viewer {
        display: none;
        position: fixed;
        z-index: 1060;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.8);
    }

    .modal-viewer-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 900px;
        position: relative;
    }

    .modal-viewer-header {
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
        margin-bottom: 10px;
        font-size: 1.2em;
    }

    .attachment-viewer-close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .attachment-viewer-close:hover,
    .attachment-viewer-close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }



    @media (max-width: 768px) {
        #chat-container {
            flex-direction: column;
            height: 90vh;
            /* Or another appropriate height */
        }

        .sidebar-panel {
            width: 100% !important;
            height: 50% !important;
            /* Employee list takes half screen */
            border-right: none;
            border-bottom: 1px solid #ccc;
            display: flex;
            flex-direction: column;
        }

        .tab-content.active {
            flex-grow: 1;
            min-height: 0;
            overflow-y: auto;
            display: block !important;
        }

        #chat-box {
            width: 100% !important;
            height: 50% !important;
            /* Chat area takes other half */
            display: flex;
            flex-direction: column;
        }

        #tab-buttons {
            display: flex !important;
        }
    }
</style>

<div id="reaction-popup" style="
                position: absolute;
                background: white;
                border: 1px solid #ccc;
                padding: 5px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                display: none;
                z-index: 1000;
            ">
    <span class="reaction-choice" style="font-size: 24px; cursor: pointer; margin: 3px;" data-id="1">👍</span>
    <span class="reaction-choice" style="font-size: 24px; cursor: pointer; margin: 3px;" data-id="2">❤️</span>
    <span class="reaction-choice" style="font-size: 24px; cursor: pointer; margin: 3px;" data-id="3">😂</span>
    <span class="reaction-choice" style="font-size: 24px; cursor: pointer; margin: 3px;" data-id="4">😮</span>
    <span class="reaction-choice" style="font-size: 24px; cursor: pointer; margin: 3px;" data-id="5">😢</span>
    <span class="reaction-choice" style="font-size: 24px; cursor: pointer; margin: 3px;" data-id="6">🔥</span>
    <span class="reaction-choice" style="font-size: 24px; cursor: pointer; margin: 3px;" data-id="7">🎉</span>
    <span class="reaction-choice" style="font-size: 24px; cursor: pointer; margin: 3px;" data-id="8">😡</span>
</div>

<div class="modal fade" id="messageOptionsModal" tabindex="-1" aria-labelledby="messageOptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="messageOptionsModalLabel">Message Options</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action" id="modal-option-reply">Reply</a>
                    <a href="#" class="list-group-item list-group-item-action" id="modal-option-forward">Forward</a>
                    <a href="#" class="list-group-item list-group-item-action text-danger" id="modal-option-delete">Delete Message</a>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="attachment-viewer-modal" class="modal-viewer">
    <div class="modal-viewer-content">
        <div class="modal-viewer-header">
            <span id="viewer-filename"></span>
            <span class="attachment-viewer-close">&times;</span>
        </div>
        <div class="modal-viewer-body">
            <img id="viewer-image" style="display:none; max-width: 100%; max-height: 80vh;">
            <iframe id="viewer-pdf" style="display:none; width: 100%; height: 80vh;" frameborder="0"></iframe>
        </div>
    </div>
</div>


<div class="container-fluid vh-95">

    <div id="chat-container">
        <div class="sidebar-panel" style="width: 25%; border-right: 1px solid #ccc; background: #f9f9f9; display: flex; flex-direction: column; height: 90vh;">
            <div id="tab-buttons" class="d-flex border-bottom">
                <button class="tab-btn active-tab" data-target="inbox-tab">Inbox</button>
                <button class="tab-btn" data-target="user-tab">Employees</button>
                <button class="tab-btn" data-target="group-tab">Tickets</button>
            </div>

            <div id="inbox-tab" class="tab-content active p-2">
                <div id="inbox-list">Loading inbox...</div>
            </div>

            <div id="user-tab" class="tab-content p-2">
                <input type="text" class="form-control mb-2" id="userSearchInput" placeholder="Search users...">
                <div id="user-list">Loading employees...</div>
            </div>
        </div>

        <div id="chat-box">

            <div id="chat-header" class="d-flex align-items-center p-2 border-bottom bg-muted" style="display: none;">
                <img src="assets/imgs/user_default.png" id="chat-header-pic" class="profile-pic me-2 border-2" alt="Profile">
                <strong id="chat-header-name"></strong>
            </div>

            <div id="messages"></div>

            <form id="chat-form">
                <input type="hidden" id="sender" value="<?php echo htmlspecialchars($user_id); ?>">
                <input type="hidden" id="receiver">
                <div class="d-flex flex-column w-100">
                    <div class="d-flex align-items-center">

                        <div class="attachment-input-wrapper me-2">
                            <label for="attachment-input" class="btn btn-outline-secondary btn-sm px-2 py-1 rounded-circle" style="font-size: 1.2em; cursor: pointer; margin-bottom: 0;">📎</label>
                            <input type="file" id="attachment-input" accept="image/jpeg,image/png,image/gif,application/pdf" style="display: none;">
                        </div>
                        <textarea class="rounded-4" id="message" placeholder="Type your message..." autocomplete="off" rows="1"></textarea>
                        <button type="submit" class="rounded-4">Send</button>
                    </div>
                    <div id="attachment-preview" class="attachment-preview mt-2 mb-1"></div>
                </div>
            </form>
        </div>
    </div>

</div>
</div>

<div id="message-options-popup" style="position: absolute; background: white; border: 1px solid #ccc; padding: 5px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); display: none; z-index: 1000;">
    <a href="#" class="dropdown-item" id="message-option-reply">Reply</a>
    <a href="#" class="dropdown-item" id="message-option-forward">Forward</a>
    <a href="#" class="dropdown-item text-danger" id="message-option-delete">Delete</a>
</div>