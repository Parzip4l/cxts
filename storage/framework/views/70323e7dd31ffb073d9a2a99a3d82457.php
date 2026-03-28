<div class="mb-3">
    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start mb-3">
        <div>
            <div class="fw-semibold">Approval Matrix</div>
            <div class="small text-muted">
                <?php echo e($scopeLabel); ?> bisa menentukan apakah ticket perlu approval, boleh direct assign, dan siapa approver yang dipakai.
                <?php echo e($inheritLabel ? 'Kosongkan override agar mengikuti ' . $inheritLabel . '.' : ''); ?>

            </div>
        </div>
        <span class="badge bg-primary-subtle text-primary"><?php echo e($scopeLabel); ?></span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="border rounded p-3 h-100 bg-white">
                <div class="text-muted small mb-1">Approval Gate</div>
                <div class="fw-semibold" data-summary-approval>Following parent rule</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100 bg-white">
                <div class="text-muted small mb-1">Assignment Gate</div>
                <div class="fw-semibold" data-summary-assignment>Following parent rule</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 h-100 bg-white">
                <div class="text-muted small mb-1">Approver Resolution</div>
                <div class="fw-semibold" data-summary-approver>Fallback to supervisor/admin</div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Strategy</th>
                    <th>When To Use</th>
                    <th>How It Resolves</th>
                </tr>
            </thead>
            <tbody>
                <tr data-strategy-row="fallback">
                    <td><span class="badge bg-secondary-subtle text-secondary">Fallback</span></td>
                    <td>Rule cukup umum dan belum butuh approver spesifik.</td>
                    <td>Supervisor/Admin yang punya hak approve tetap bisa mengambil keputusan.</td>
                </tr>
                <tr data-strategy-row="specific_user">
                    <td><span class="badge bg-info-subtle text-info">Specific User</span></td>
                    <td>Approval harus jatuh ke 1 orang tertentu.</td>
                    <td>Pilih user approver langsung dari daftar approver.</td>
                </tr>
                <tr data-strategy-row="requester_department_head">
                    <td><span class="badge bg-warning-subtle text-warning">Department Head</span></td>
                    <td>Approval mengikuti atasan dari department requester.</td>
                    <td>Sistem mengambil `head_user_id` dari department requester.</td>
                </tr>
                <tr data-strategy-row="service_manager">
                    <td><span class="badge bg-success-subtle text-success">Service Manager</span></td>
                    <td>Request harus disetujui owner layanan terkait.</td>
                    <td>Sistem mengambil `service_manager_user_id` dari related service.</td>
                </tr>
                <tr data-strategy-row="role_based">
                    <td><span class="badge bg-primary-subtle text-primary">Role Based</span></td>
                    <td>Approval boleh dilakukan oleh role tertentu, bukan user tertentu.</td>
                    <td>Pilih role approver, misalnya `Supervisor` atau `Operational Admin`.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php if (! $__env->hasRenderedOnce('be29fae9-0c00-4681-a345-54a3ab885c1f')): $__env->markAsRenderedOnce('be29fae9-0c00-4681-a345-54a3ab885c1f'); ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-approval-config-root]').forEach((root) => {
                    const approvalField = root.querySelector('select[name="requires_approval"]');
                    const approvalCheckbox = root.querySelector('input[type="checkbox"][name="requires_approval"]');
                    const assignmentField = root.querySelector('select[name="allow_direct_assignment"]');
                    const assignmentCheckbox = root.querySelector('input[type="checkbox"][name="allow_direct_assignment"]');
                    const strategyField = root.querySelector('[name="approver_strategy"]');
                    const approverUserField = root.querySelector('[name="approver_user_id"]');
                    const approverRoleField = root.querySelector('[name="approver_role_code"]');
                    const approverUserWrap = root.querySelector('[data-approver-user-field]');
                    const approverRoleWrap = root.querySelector('[data-approver-role-field]');

                    const approvalSummary = root.querySelector('[data-summary-approval]');
                    const assignmentSummary = root.querySelector('[data-summary-assignment]');
                    const approverSummary = root.querySelector('[data-summary-approver]');

                    const selectedLabel = (field) => {
                        const option = field?.selectedOptions?.[0];

                        return option ? option.textContent.trim() : '';
                    };

                    const binaryValue = (selectField, checkboxField) => {
                        if (checkboxField) {
                            return checkboxField.checked ? '1' : '0';
                        }

                        return selectField?.value ?? '';
                    };

                    const updateSummary = () => {
                        const approvalValue = binaryValue(approvalField, approvalCheckbox);
                        const assignmentValue = binaryValue(assignmentField, assignmentCheckbox);
                        const strategyValue = strategyField?.value ?? 'fallback';
                        const approverUserLabel = selectedLabel(approverUserField);
                        const approverRoleLabel = selectedLabel(approverRoleField);

                        if (approvalSummary) {
                            approvalSummary.textContent = approvalValue === '1'
                                ? 'Approval required before assignment'
                                : approvalValue === '0'
                                    ? 'No approval required'
                                    : 'Following parent rule';
                        }

                        if (assignmentSummary) {
                            assignmentSummary.textContent = assignmentValue === '1'
                                ? 'Ticket can be assigned directly'
                                : assignmentValue === '0'
                                    ? 'Needs ready flag before assign'
                                    : 'Following parent rule';
                        }

                        if (approverSummary) {
                            approverSummary.textContent = ({
                                fallback: 'Fallback to supervisor/admin',
                                specific_user: approverUserLabel || 'Select specific approver',
                                requester_department_head: 'Requester department head',
                                service_manager: 'Related service manager',
                                role_based: approverRoleLabel || 'Select approver role',
                            })[strategyValue] || 'Fallback to supervisor/admin';
                        }

                        if (approverUserWrap) {
                            approverUserWrap.classList.toggle('d-none', strategyValue !== 'specific_user');
                        }

                        if (approverRoleWrap) {
                            approverRoleWrap.classList.toggle('d-none', strategyValue !== 'role_based');
                        }

                        root.querySelectorAll('[data-strategy-row]').forEach((row) => {
                            row.classList.toggle('table-primary', row.dataset.strategyRow === strategyValue);
                        });
                    };

                    [approvalField, approvalCheckbox, assignmentField, assignmentCheckbox, strategyField, approverUserField, approverRoleField]
                        .filter(Boolean)
                        .forEach((field) => field.addEventListener('change', updateSummary));

                    updateSummary();
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/partials/approval-matrix.blade.php ENDPATH**/ ?>