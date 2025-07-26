function COE_BenefitClaimRequest(employee_id) {
    fetch('request_coe_benefitclaim.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'employee_id=' + encodeURIComponent(employee_id)
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById('main-content').innerHTML = data;

            //Generate Request ID
            const reqIdInput = document.getElementById('req_id');
            reqIdInput.value = employee_id + '_' + generateCOEId();

            fetch('fetch/policy/coe_dateneeded.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'coe_type=BENEFITS'
            })
                .then(res => res.text())
                .then(days => {
                    const leadDays = parseInt(days) || 0;
                    const dateInput = document.getElementById('date_needed');

                    const today = new Date();
                    today.setDate(today.getDate() + leadDays);
                    const minDate = today.toISOString().split('T')[0];

                    dateInput.min = minDate;
                    dateInput.value = minDate;

                    const tooltipIcon = document.getElementById('date_notice_icon');
                    tooltipIcon.setAttribute('title', `COE for Benefit Claim must be requested at least ${leadDays} day(s) in advance.`);

                    // Re-initialize tooltip (Bootstrap)
                    new bootstrap.Tooltip(tooltipIcon);
                });

            BENIFITCLAIM_COMPENSATION('ck_compensation');
            //Modal Summary
            ShowSummary('submit_btn');
            //Submit Request
            ConfirmRequest('submit_request');
            //Attach textarea auto-resize
            bindTextareaAutoResize('req_reason');

            const coeType = 'BENEFITS CLAIM';
            loadChecklist(coeType);

            // File Upload
            const fileInput = document.getElementById('files');
            const browseBtn = document.getElementById('btnBrowseFiles'); // Your browse button id

            if (fileInput && browseBtn) {
                browseBtn.addEventListener('click', () => fileInput.click());
                fileInput.addEventListener('change', handleFileSelect); // your file handler function
            }

        })
        .catch(error => {
            document.getElementById('main-content').innerHTML = '<p>Error loading request view.</p>';
            console.error('Fetch error:', error);
        });
}

function COE_TravelRequest(employee_id) {
    fetch('request_coe_travel.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'employee_id=' + encodeURIComponent(employee_id)
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById('main-content').innerHTML = data;

            //Generate Request ID
            const reqIdInput = document.getElementById('req_id');
            reqIdInput.value = employee_id + '_' + generateCOEId();


            fetch('fetch/policy/coe_dateneeded.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'coe_type=TRAVEL'
            })
                .then(res => res.text())
                .then(days => {
                    const leadDays = parseInt(days) || 0;
                    const dateInput = document.getElementById('date_needed');

                    const today = new Date();
                    today.setDate(today.getDate() + leadDays);
                    const minDate = today.toISOString().split('T')[0];

                    dateInput.min = minDate;
                    dateInput.value = minDate;

                    const tooltipIcon = document.getElementById('date_notice_icon');
                    tooltipIcon.setAttribute('title', `Based Memo 222: COE for travel must be requested at least ${leadDays} day(s) in advance.`);

                    // Re-initialize tooltip (Bootstrap)
                    new bootstrap.Tooltip(tooltipIcon);
                });

            //Modal Summary
            ShowSummary('submit_btn');
            //Submit Request
            ConfirmRequest('submit_request');
            //Attach textarea auto-resize
            bindTextareaAutoResize('req_reason');

            const coeType = 'TRAVEL';
            loadChecklist(coeType);

            // File Upload
            const fileInput = document.getElementById('files');
            const browseBtn = document.getElementById('btnBrowseFiles');

            if (fileInput && browseBtn) {
                browseBtn.addEventListener('click', () => fileInput.click());
                fileInput.addEventListener('change', handleFileSelect);
            }

        })
        .catch(error => {
            document.getElementById('main-content').innerHTML = '<p>Error loading request view.</p>';
            console.error('Fetch error:', error);
        });
}


function COE_FinancialRequest(employee_id) {
    fetch('request_coe_financial.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'employee_id=' + encodeURIComponent(employee_id)
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById('main-content').innerHTML = data;

            //Generate Request ID
            const reqIdInput = document.getElementById('req_id');
            reqIdInput.value = employee_id + '_' + generateCOEId();

            fetch('fetch/policy/coe_dateneeded.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'coe_type=FINANCIAL'
            })
                .then(res => res.text())
                .then(days => {
                    const leadDays = parseInt(days) || 0;
                    const dateInput = document.getElementById('date_needed');

                    const today = new Date();
                    today.setDate(today.getDate() + leadDays);
                    const minDate = today.toISOString().split('T')[0];

                    dateInput.min = minDate;
                    dateInput.value = minDate;

                    const tooltipIcon = document.getElementById('date_notice_icon');
                    tooltipIcon.setAttribute('title', `COE for Financial must be requested at least ${leadDays} day(s) in advance.`);

                    // Re-initialize tooltip (Bootstrap)
                    new bootstrap.Tooltip(tooltipIcon);
                });

            //Modal Summary
            ShowSummary('submit_btn');
            //Submit Request
            ConfirmRequest('submit_request');
            //Attach textarea auto-resize
            bindTextareaAutoResize('req_reason');

            const coeType = 'FINANCIAL';
            loadChecklist(coeType);

            // File Upload
            const fileInput = document.getElementById('files');
            const browseBtn = document.getElementById('btnBrowseFiles'); // Your browse button id

            if (fileInput && browseBtn) {
                browseBtn.addEventListener('click', () => fileInput.click());
                fileInput.addEventListener('change', handleFileSelect); // your file handler function
            }

        })
        .catch(error => {
            document.getElementById('main-content').innerHTML = '<p>Error loading request view.</p>';
            console.error('Fetch error:', error);
        });
}

function COE_TrainingRequest(employee_id) {
    fetch('request_coe_training.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'employee_id=' + encodeURIComponent(employee_id)
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById('main-content').innerHTML = data;

            //Generate Request ID
            const reqIdInput = document.getElementById('req_id');
            reqIdInput.value = employee_id + '_' + generateCOEId();

            fetch('fetch/policy/coe_dateneeded.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'coe_type=TRAINING'
            })
                .then(res => res.text())
                .then(days => {
                    const leadDays = parseInt(days) || 0;
                    const dateInput = document.getElementById('date_needed');

                    const today = new Date();
                    today.setDate(today.getDate() + leadDays);
                    const minDate = today.toISOString().split('T')[0];

                    dateInput.min = minDate;
                    dateInput.value = minDate;

                    const tooltipIcon = document.getElementById('date_notice_icon');
                    tooltipIcon.setAttribute('title', `COE for Training/Educational must be requested at least ${leadDays} day(s) in advance.`);

                    // Re-initialize tooltip (Bootstrap)
                    new bootstrap.Tooltip(tooltipIcon);
                });


            //Modal Summary
            ShowSummary('submit_btn');
            //Submit Request
            ConfirmRequest('submit_request');
            //Attach textarea auto-resize
            bindTextareaAutoResize('req_reason');

            const coeType = 'TRAINING/EDUCATIONAL';
            loadChecklist(coeType);


            // File Upload
            const fileInput = document.getElementById('files');
            const browseBtn = document.getElementById('btnBrowseFiles'); // Your browse button id

            if (fileInput && browseBtn) {
                browseBtn.addEventListener('click', () => fileInput.click());
                fileInput.addEventListener('change', handleFileSelect); // your file handler function
            }

        })
        .catch(error => {
            document.getElementById('main-content').innerHTML = '<p>Error loading request view.</p>';
            console.error('Fetch error:', error);
        });
}

function COE_View(request_id, employee_id, coe_type) {

    let url = '';

    if (coe_type === 'BENEFIT CLAIM' || coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
        url = 'request_coe_benefitclaim_view.php';
    } else if (coe_type === 'TRAVEL') {
        url = 'request_coe_travel_view.php';
    } else if (coe_type === 'FINANCIAL') {
        url = 'request_coe_financial_view.php';
    } else if (coe_type === 'TRAINING/EDUCATIONAL') {
        url = 'request_coe_training_view.php';
    } else {
        console.error('Unsupported COE type:', coe_type);
        return;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'request_id=' + encodeURIComponent(request_id) +
            '&employee_id=' + encodeURIComponent(employee_id)
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById('main-content').innerHTML = data;

            if (coe_type === 'BENEFIT CLAIM' || coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
                Benefit_Vault_Password('vault_unlock_btn', employee_id, request_id);
            }

            bindTextareaAutoResize('approval_remarks');
            bindTextareaAutoResize('decline_reason');
            ApprovedCOERequest('approved_request');
            DeclinedCOERequest('declined_request');
        })
        .catch(error => {
            document.getElementById('main-content').innerHTML = '<p>Error loading request view.</p>';
            console.error('Fetch error:', error);
        });
}

function COE_ApprovalView(request_id, employee_id, coe_type) {

    let url = '';

    if (coe_type === 'BENEFIT CLAIM' || coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
        url = 'request_coe_benefitclaim_approvalview.php';
    } else if (coe_type === 'TRAVEL') {
        url = 'request_coe_travel_approvalview.php';
    } else if (coe_type === 'FINANCIAL') {
        url = 'request_coe_financial_approvalview.php';
    } else if (coe_type === 'TRAINING/EDUCATIONAL') {
        url = 'request_coe_training_approvalview.php';
    } else {
        console.error('Unsupported COE type:', coe_type);
        return;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'request_id=' + encodeURIComponent(request_id) +
            '&employee_id=' + encodeURIComponent(employee_id)
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById('main-content').innerHTML = data;

            bindTextareaAutoResize('approval_remarks');
            bindTextareaAutoResize('decline_reason');
            ApprovedCOERequest('approved_request');
            DeclinedCOERequest('declined_request');
        })
        .catch(error => {
            document.getElementById('main-content').innerHTML = '<p>Error loading request view.</p>';
            console.error('Fetch error:', error);
        });
}

function COE_HRView(request_id, employee_id, coe_type) {

    let url = '';

    if (coe_type === 'BENEFIT CLAIM' || coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
        url = 'request_coe_benefitclaim_hrview.php';
    } else if (coe_type === 'TRAVEL') {
        url = 'request_coe_travel_hrview.php';
    } else if (coe_type === 'FINANCIAL') {
        url = 'request_coe_financial_hrview.php';
    } else if (coe_type === 'TRAINING/EDUCATIONAL') {
        url = 'request_coe_training_hrview.php';
    } else {
        console.error('Unsupported COE type:', coe_type);
        return;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'request_id=' + encodeURIComponent(request_id) +
            '&employee_id=' + encodeURIComponent(employee_id)
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById('main-content').innerHTML = data;

            bindTextareaAutoResize('approval_remarks');
            bindTextareaAutoResize('decline_reason');
            bindTextareaAutoResize('onprocess_remarks');
            bindTextareaAutoResize('forsigning_remarks');
            bindTextareaAutoResize('forreleasing_remarks');
            bindTextareaAutoResize('releasing_remarks');
            bindTextareaAutoResize('denied_remarks');

            if (coe_type === 'BENEFIT CLAIM' || coe_type === 'BENEFIT CLAIM WITH COMPENSATION') {
                bindTextareaAutoResize('compensation_details');
                EnableCompensationEditing('btn_edit_compensation');
                COE_UpdateCompensation('btn_update_compensation');
            }

            if (coe_type === 'FINANCIAL') {
                bindTextareaAutoResize('compensation_details');
                EnableCompensationEditing('btn_edit_compensation');
                COE_UpdatePurpose('btn_update_compensation');
            }

            if (coe_type === 'TRAINING/EDUCATIONAL') {
                bindTextareaAutoResize('compensation_details');
                EnableCompensationEditing('btn_edit_compensation');
                COE_UpdateTitleAndPurposes('btn_update_compensation');
            }

            COE_OnProcess('onprocess_request');
            COE_ForSigning('forsigning_request');
            COE_ForReleasing('forreleasing_request');
            COE_Released('released_request');
            COE_OnHold('onhold_request');

            COE_TravelPrint('btn_generate_coe');
            COE_ViewLeave('btn_view_leave');
        })
        .catch(error => {
            document.getElementById('main-content').innerHTML = '<p>Error loading request view.</p>';
            console.error('Fetch error:', error);
        });
}



