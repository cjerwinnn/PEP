<div id="alert-placeholder" style="position: fixed; top: 10px; right: 10px; z-index: 1056;"></div>
<div id="alert-success-placeholder" style="position: fixed; top: 10px; right: 10px; z-index: 1056;"></div>

<!-- 
    <?php if (!isset($_SESSION['privacy_acknowledged'])): ?>
        <script>
            window.addEventListener('load', function() {
                var modal = new bootstrap.Modal(document.getElementById('dataPrivacyModal'));
                modal.show();
            });
        </script>
    <?php endif; ?> -->

<!-- Data Privacy Notice Modal -->
<div class="modal fade" id="dataPrivacyModal" tabindex="-1"
    aria-labelledby="dataPrivacyModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="dataPrivacyModalLabel">🔒 Data Privacy Notice</h5>
            </div>
            <div class="modal-body">
                <p>PDMC values and upholds your right to data privacy. As part of our compliance with the Data Privacy Act of 2012, we ensure that all personal information you provide on this portal is collected, processed, and stored with utmost confidentiality and security.</p>
                <ul>
                    <li>Your data will only be used for legitimate HR and administrative purposes.</li>
                    <li>We will not disclose your personal information to third parties without your consent.</li>
                    <li>You have the right to access, update, and request deletion of your data, subject to legal and contractual obligations.</li>
                </ul>
                <p>By using this employee portal, you acknowledge and agree to the data privacy practices outlined above.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">I Acknowledge</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="setVaultPasswordModal" tabindex="-1" aria-labelledby="setVaultPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="setVaultPasswordModalLabel">Set Vault Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="setVaultPasswordForm">
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" required>
                        <div id="password-strength-meter" class="password-strength-meter"></div>
                        <div id="password-strength-text" class="form-text"></div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" required>
                        <div id="password-match-text" class="form-text"></div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="showPassword">
                        <label class="form-check-label" for="showPassword">
                            Show Password
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savePasswordBtn">Save Password</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/js/main.js"></script>
