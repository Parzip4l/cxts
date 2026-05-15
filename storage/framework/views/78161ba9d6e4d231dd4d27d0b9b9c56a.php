<?php
    $isModal = $isModal ?? false;
    $formId = $formId ?? 'ticket-create-form';
    $returnTo = $returnTo ?? null;
?>

<?php
    $userRole = auth()->user()?->role;
    $canUseOperationalTriage = in_array($userRole, ['super_admin', 'operational_admin', 'supervisor'], true);
    $selectedContextMode = old('context_mode');

    if ($selectedContextMode === null) {
        if ($ticket->asset_id) {
            $selectedContextMode = 'asset';
        } elseif ($ticket->service_id) {
            $selectedContextMode = 'service';
        } elseif ($ticket->asset_location_id) {
            $selectedContextMode = 'location';
        } else {
            $selectedContextMode = 'none';
        }
    }

    $selectedPriorityId = old('ticket_priority_id', $ticket->ticket_priority_id ?: $defaultPriorityId);
    $selectedSource = old('source', $ticket->source ?: 'web');
    $selectedImpact = old('impact', $ticket->impact ?: 'medium');
    $selectedUrgency = old('urgency', $ticket->urgency ?: 'medium');
    $selectedPriorityLabel = optional($priorityOptions->firstWhere('id', $selectedPriorityId))->name ?? 'Medium';
    $selectedAssignedEngineerIds = collect(old('assigned_engineer_ids', []))
        ->map(fn ($id) => (string) $id)
        ->all();

    $initialStep = 1;
    if ($errors->hasAny(['service_id', 'asset_id', 'asset_location_id', 'context_mode'])) {
        $initialStep = 2;
    }
    if ($errors->hasAny(['requester_id', 'requester_department_id', 'ticket_priority_id', 'source', 'impact', 'urgency', 'ticket_detail_subcategory_id'])) {
        $initialStep = 3;
    }
    if ($errors->hasAny(['assigned_engineer_ids', 'assigned_engineer_ids.*', 'assigned_team_name', 'assignment_notes'])) {
        $initialStep = 4;
    }
    if ($errors->hasAny(['attachments', 'attachments.*'])) {
        $initialStep = 1;
    }

    $groupedEngineerOptions = $engineerOptions->groupBy(
        fn ($engineer) => $engineer->department?->name ?? 'No Engineer Team'
    );
    $engineerTeamNameOptions = $groupedEngineerOptions->keys()->filter()->values();
?>

<?php if(! $isModal): ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                <div>
                    <div class="text-uppercase small text-muted fw-semibold mb-1">Simplified Ticket Creation</div>
                    <h4 class="mb-2">Create Ticket In 4 Steps</h4>
                </div>
                <div class="small text-muted">
                    <div>1. Masalah apa yang terjadi</div>
                    <div>2. Apa yang terdampak</div>
                    <div>3. Review dan triage operasional</div>
                    <div>4. Assignment engineer opsional</div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card <?php echo e($isModal ? 'border-0 shadow-none mb-0' : ''); ?>">
    <div class="card-body <?php echo e($isModal ? 'p-0' : 'p-4'); ?>">
        <form method="POST" action="<?php echo e($action); ?>" enctype="multipart/form-data" class="row g-4" id="<?php echo e($formId); ?>" data-ticket-create-form data-initial-step="<?php echo e($initialStep); ?>">
            <?php echo csrf_field(); ?>
            <?php if($returnTo): ?>
                <input type="hidden" name="return_to" value="<?php echo e($returnTo); ?>">
            <?php endif; ?>

            <div class="col-12">
                <div class="d-flex flex-column flex-lg-row gap-2 gap-lg-3" data-stepper>
                    <button type="button" class="btn btn-outline-primary text-start px-3 py-3 flex-fill" data-step-trigger="1">
                        <div class="fw-semibold">Step 1</div>
                        <div class="small text-muted">Issue Basics</div>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start px-3 py-3 flex-fill" data-step-trigger="2">
                        <div class="fw-semibold">Step 2</div>
                        <div class="small text-muted">Affected Context</div>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start px-3 py-3 flex-fill" data-step-trigger="3">
                        <div class="fw-semibold">Step 3</div>
                        <div class="small text-muted">Review & Triage</div>
                    </button>
                    <button type="button" class="btn btn-outline-primary text-start px-3 py-3 flex-fill" data-step-trigger="4">
                        <div class="fw-semibold">Step 4</div>
                        <div class="small text-muted">Engineer Assignment</div>
                    </button>
                </div>
            </div>

            <div class="col-12" data-step-panel="1">
                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 1</div>
                            <h5 class="mb-1">Report The Issue</h5>
                        </div>
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">Core Input</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="<?php echo e($formId); ?>-title" class="form-label">Issue Summary</label>
                            <input
                                type="text"
                                id="<?php echo e($formId); ?>-title"
                                name="title"
                                class="form-control <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('title', $ticket->title)); ?>"
                                placeholder="Contoh: Internet kantor lantai 3 putus"
                                required
                            >
                            <div class="form-text">Gunakan kalimat singkat yang langsung menjelaskan masalah utama.</div>
                            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-4">
                            <label for="<?php echo e($formId); ?>-ticket_category_id" class="form-label">Ticket Type</label>
                            <select id="<?php echo e($formId); ?>-ticket_category_id" name="ticket_category_id" class="form-select <?php $__errorArgs = ['ticket_category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <option value="">- Select -</option>
                                <?php $__currentLoopData = $categoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option->id); ?>" <?php if((string) old('ticket_category_id', $ticket->ticket_category_id) === (string) $option->id): echo 'selected'; endif; ?>>
                                        <?php echo e($option->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Pilih jenis ticket yang paling mendekati kebutuhan atau gangguan.</div>
                            <?php $__errorArgs = ['ticket_category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6 d-none" data-subcategory-wrapper>
                            <label for="<?php echo e($formId); ?>-ticket_subcategory_id" class="form-label">Ticket Category</label>
                            <select id="<?php echo e($formId); ?>-ticket_subcategory_id" name="ticket_subcategory_id" class="form-select <?php $__errorArgs = ['ticket_subcategory_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">- Optional -</option>
                                <?php $__currentLoopData = $subcategoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option->id); ?>" data-category-id="<?php echo e($option->ticket_category_id); ?>" <?php if((string) old('ticket_subcategory_id', $ticket->ticket_subcategory_id) === (string) $option->id): echo 'selected'; endif; ?>>
                                        <?php echo e($option->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Opsional. Pilih jika Anda sudah tahu kategori yang lebih spesifik.</div>
                            <?php $__errorArgs = ['ticket_subcategory_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-md-6 d-none" data-detail-subcategory-wrapper>
                            <label for="<?php echo e($formId); ?>-ticket_detail_subcategory_id" class="form-label">Ticket Sub Category</label>
                            <select id="<?php echo e($formId); ?>-ticket_detail_subcategory_id" name="ticket_detail_subcategory_id" class="form-select <?php $__errorArgs = ['ticket_detail_subcategory_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">- Optional -</option>
                                <?php $__currentLoopData = $detailSubcategoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option->id); ?>" data-subcategory-id="<?php echo e($option->ticket_subcategory_id); ?>" <?php if((string) old('ticket_detail_subcategory_id', $ticket->ticket_detail_subcategory_id) === (string) $option->id): echo 'selected'; endif; ?>>
                                        <?php echo e($option->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Opsional. Gunakan jika Anda ingin klasifikasi yang lebih detail untuk reporting dan analisis.</div>
                            <?php $__errorArgs = ['ticket_detail_subcategory_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-12">
                            <label for="<?php echo e($formId); ?>-description" class="form-label">Issue Description</label>
                            <textarea
                                id="<?php echo e($formId); ?>-description"
                                name="description"
                                rows="5"
                                class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="Jelaskan gejala masalah, dampak ke user, dan kapan mulai terjadi"
                                required
                            ><?php echo e(old('description', $ticket->description)); ?></textarea>
                            <div class="form-text">Tulis gejala yang terlihat, dampak ke pekerjaan user, dan petunjuk apa pun yang bisa membantu tim.</div>
                            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="col-12">
                            <label for="<?php echo e($formId); ?>-attachments" class="form-label">Lampiran Foto</label>
                            <input
                                type="file"
                                id="<?php echo e($formId); ?>-attachments"
                                name="attachments[]"
                                class="form-control <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                multiple
                            >
                            <div class="form-text">Maksimal 1MB. Format yang diizinkan: JPG, PNG, WEBP.</div>
                            <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 d-none" data-step-panel="2">
                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 2</div>
                            <h5 class="mb-1">What Is Affected?</h5>
                            <p class="text-muted mb-0">Pilih satu konteks utama agar tim operasional lebih cepat memahami area yang terdampak.</p>
                        </div>
                        <div class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">Optional Context</div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <input type="radio" class="btn-check" name="context_mode" id="<?php echo e($formId); ?>-context_mode_none" value="none" <?php if($selectedContextMode === 'none'): echo 'checked'; endif; ?>>
                        <label class="btn btn-outline-secondary" for="<?php echo e($formId); ?>-context_mode_none">No Specific Context</label>

                        <input type="radio" class="btn-check" name="context_mode" id="<?php echo e($formId); ?>-context_mode_service" value="service" <?php if($selectedContextMode === 'service'): echo 'checked'; endif; ?>>
                        <label class="btn btn-outline-primary" for="<?php echo e($formId); ?>-context_mode_service">Related Service</label>

                        <input type="radio" class="btn-check" name="context_mode" id="<?php echo e($formId); ?>-context_mode_asset" value="asset" <?php if($selectedContextMode === 'asset'): echo 'checked'; endif; ?>>
                        <label class="btn btn-outline-primary" for="<?php echo e($formId); ?>-context_mode_asset">Related Asset</label>

                        <input type="radio" class="btn-check" name="context_mode" id="<?php echo e($formId); ?>-context_mode_location" value="location" <?php if($selectedContextMode === 'location'): echo 'checked'; endif; ?>>
                        <label class="btn btn-outline-primary" for="<?php echo e($formId); ?>-context_mode_location">Asset Location</label>
                    </div>

                    <input type="hidden" id="<?php echo e($formId); ?>-asset_location_id" name="asset_location_id" value="<?php echo e(old('asset_location_id', $ticket->asset_location_id)); ?>">

                    <div class="alert alert-light border mb-0" data-context-panel="none">
                        Ticket akan dibuat tanpa service, asset, atau lokasi spesifik. Cocok untuk permintaan umum atau kendala yang objek terdampaknya belum jelas.
                    </div>

                    <div class="row g-3 d-none" data-context-panel="service">
                        <div class="col-lg-8">
                            <label for="<?php echo e($formId); ?>-service_id" class="form-label">Related Service</label>
                            <select id="<?php echo e($formId); ?>-service_id" name="service_id" class="form-select <?php $__errorArgs = ['service_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-searchable-select data-search-placeholder="Search service">
                                <option value="">- Select Related Service -</option>
                                <?php $__currentLoopData = $serviceOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option->id); ?>" <?php if((string) old('service_id', $ticket->service_id) === (string) $option->id): echo 'selected'; endif; ?>>
                                        <?php echo e($option->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Gunakan jika gangguan atau request terkait layanan tertentu.</div>
                            <?php $__errorArgs = ['service_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="row g-3 d-none" data-context-panel="asset">
                        <div class="col-lg-6">
                            <label for="<?php echo e($formId); ?>-asset_id" class="form-label">Related Asset</label>
                            <select id="<?php echo e($formId); ?>-asset_id" name="asset_id" class="form-select <?php $__errorArgs = ['asset_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-searchable-select data-search-placeholder="Search asset">
                                <option value="">- Select Related Asset -</option>
                                <?php $__currentLoopData = $assetOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($option->id); ?>"
                                        data-service-id="<?php echo e($option->service_id); ?>"
                                        data-location-id="<?php echo e($option->asset_location_id); ?>"
                                        <?php if((string) old('asset_id', $ticket->asset_id) === (string) $option->id): echo 'selected'; endif; ?>
                                    >
                                        <?php echo e($option->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Pilih perangkat atau unit yang paling dekat dengan masalah.</div>
                            <?php $__errorArgs = ['asset_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-lg-6">
                            <label for="<?php echo e($formId); ?>-asset_location_id_asset_mode" class="form-label">Asset Location</label>
                            <select id="<?php echo e($formId); ?>-asset_location_id_asset_mode" class="form-select <?php $__errorArgs = ['asset_location_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-searchable-select data-search-placeholder="Search asset location">
                                <option value="">- Optional Location -</option>
                                <?php $__currentLoopData = $locationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option->id); ?>" <?php if($selectedContextMode === 'asset' && (string) old('asset_location_id', $ticket->asset_location_id) === (string) $option->id): echo 'selected'; endif; ?>>
                                        <?php echo e($option->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Opsional. Isi jika aset berada di site atau area yang spesifik.</div>
                            <?php $__errorArgs = ['asset_location_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="row g-3 d-none" data-context-panel="location">
                        <div class="col-lg-8">
                            <label for="<?php echo e($formId); ?>-asset_location_id_location_mode" class="form-label">Asset Location</label>
                            <select id="<?php echo e($formId); ?>-asset_location_id_location_mode" class="form-select <?php $__errorArgs = ['asset_location_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-searchable-select data-search-placeholder="Search asset location">
                                <option value="">- Select Location -</option>
                                <?php $__currentLoopData = $locationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option->id); ?>" <?php if($selectedContextMode === 'location' && (string) old('asset_location_id', $ticket->asset_location_id) === (string) $option->id): echo 'selected'; endif; ?>>
                                        <?php echo e($option->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="form-text">Gunakan jika user hanya tahu site, area, atau ruang yang terdampak.</div>
                            <?php $__errorArgs = ['asset_location_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div id="<?php echo e($formId); ?>-ticket-context-smart-hint" class="alert alert-info border d-none mt-3 mb-0"></div>
                </div>
            </div>

            <div class="col-12 d-none" data-step-panel="3">
                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 3</div>
                            <h5 class="mb-1">Review & Operational Triage</h5>
                            <p class="text-muted mb-0">Langkah terakhir untuk memastikan ticket siap dibuat. User biasa cukup review ringkas, supervisor bisa menambahkan triage operasional.</p>
                        </div>
                        <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Ready To Submit</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-white">
                                <div class="small text-muted mb-1">Priority Default</div>
                                <div class="fw-semibold"><?php echo e($selectedPriorityLabel); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-white">
                                <div class="small text-muted mb-1">Impact Default</div>
                                <div class="fw-semibold text-capitalize"><?php echo e($selectedImpact); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 bg-white">
                                <div class="small text-muted mb-1">Urgency Default</div>
                                <div class="fw-semibold text-capitalize"><?php echo e($selectedUrgency); ?></div>
                            </div>
                        </div>
                    </div>

                    <?php if($canUseOperationalTriage): ?>
                        <div class="alert alert-info border mb-3">
                            Anda login sebagai role operasional, jadi pengaturan requester override, priority, source, impact, dan urgency tetap tersedia di bawah ini.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="<?php echo e($formId); ?>-requester_id" class="form-label">Requester Override</label>
                                <select id="<?php echo e($formId); ?>-requester_id" name="requester_id" class="form-select <?php $__errorArgs = ['requester_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-searchable-select data-search-placeholder="Search requester">
                                    <option value="">- Auto Current User -</option>
                                    <?php $__currentLoopData = $requesterOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($option->id); ?>" <?php if((string) old('requester_id', $ticket->requester_id ?? $defaultRequesterId) === (string) $option->id): echo 'selected'; endif; ?>>
                                            <?php echo e($option->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['requester_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6">
                                <label for="<?php echo e($formId); ?>-requester_department_id" class="form-label">Requester Department Override</label>
                                <select id="<?php echo e($formId); ?>-requester_department_id" name="requester_department_id" class="form-select <?php $__errorArgs = ['requester_department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" data-searchable-select data-search-placeholder="Search department">
                                    <option value="">- Auto Current User Department -</option>
                                    <?php $__currentLoopData = $requesterDepartmentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($option->id); ?>" <?php if((string) old('requester_department_id', $ticket->requester_department_id ?? $defaultRequesterDepartmentId) === (string) $option->id): echo 'selected'; endif; ?>>
                                            <?php echo e($option->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['requester_department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label for="<?php echo e($formId); ?>-ticket_priority_id" class="form-label">Priority</label>
                                <select id="<?php echo e($formId); ?>-ticket_priority_id" name="ticket_priority_id" class="form-select <?php $__errorArgs = ['ticket_priority_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">- Select -</option>
                                    <?php $__currentLoopData = $priorityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($option->id); ?>" <?php if((string) $selectedPriorityId === (string) $option->id): echo 'selected'; endif; ?>>
                                            <?php echo e($option->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['ticket_priority_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label for="<?php echo e($formId); ?>-source" class="form-label">Source</label>
                                <select id="<?php echo e($formId); ?>-source" name="source" class="form-select <?php $__errorArgs = ['source'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="web" <?php if($selectedSource === 'web'): echo 'selected'; endif; ?>>Web</option>
                                    <option value="email" <?php if($selectedSource === 'email'): echo 'selected'; endif; ?>>Email</option>
                                    <option value="phone" <?php if($selectedSource === 'phone'): echo 'selected'; endif; ?>>Phone</option>
                                    <option value="api" <?php if($selectedSource === 'api'): echo 'selected'; endif; ?>>API</option>
                                </select>
                                <?php $__errorArgs = ['source'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label for="<?php echo e($formId); ?>-impact" class="form-label">Impact</label>
                                <select id="<?php echo e($formId); ?>-impact" name="impact" class="form-select <?php $__errorArgs = ['impact'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="low" <?php if($selectedImpact === 'low'): echo 'selected'; endif; ?>>Low</option>
                                    <option value="medium" <?php if($selectedImpact === 'medium'): echo 'selected'; endif; ?>>Medium</option>
                                    <option value="high" <?php if($selectedImpact === 'high'): echo 'selected'; endif; ?>>High</option>
                                </select>
                                <?php $__errorArgs = ['impact'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label for="<?php echo e($formId); ?>-urgency" class="form-label">Urgency</label>
                                <select id="<?php echo e($formId); ?>-urgency" name="urgency" class="form-select <?php $__errorArgs = ['urgency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="low" <?php if($selectedUrgency === 'low'): echo 'selected'; endif; ?>>Low</option>
                                    <option value="medium" <?php if($selectedUrgency === 'medium'): echo 'selected'; endif; ?>>Medium</option>
                                    <option value="high" <?php if($selectedUrgency === 'high'): echo 'selected'; endif; ?>>High</option>
                                </select>
                                <?php $__errorArgs = ['urgency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">
                            Ticket akan menggunakan default operasional agar user tidak perlu mengisi terlalu banyak field. Tim service desk tetap bisa melakukan triage setelah ticket dibuat.
                        </div>
                        <input type="hidden" name="ticket_priority_id" value="<?php echo e($selectedPriorityId); ?>">
                        <input type="hidden" name="source" value="<?php echo e($selectedSource); ?>">
                        <input type="hidden" name="impact" value="<?php echo e($selectedImpact); ?>">
                        <input type="hidden" name="urgency" value="<?php echo e($selectedUrgency); ?>">
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 d-none" data-step-panel="4">
                <div class="border rounded-3 p-3 p-lg-4 bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="text-uppercase small text-muted fw-semibold mb-1">Step 4</div>
                            <h5 class="mb-1">Assign Engineer</h5>
                            <p class="text-muted mb-0">Pilih satu atau beberapa engineer jika ticket langsung boleh di-assign setelah dibuat.</p>
                        </div>
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">Optional Dispatch</div>
                    </div>

                    <?php if($canUseOperationalTriage): ?>
                        <div class="alert alert-info border">
                            Assignment di step ini akan diproses otomatis jika ticket tidak tertahan approval atau readiness gate. Jika ticket butuh approval, pilihan engineer tetap tidak memaksa bypass governance.
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="<?php echo e($formId); ?>-assigned_engineer_ids" class="form-label">Engineer</label>
                                <select
                                    id="<?php echo e($formId); ?>-assigned_engineer_ids"
                                    name="assigned_engineer_ids[]"
                                    class="form-select <?php $__errorArgs = ['assigned_engineer_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['assigned_engineer_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    data-searchable-select
                                    data-force-searchable-select="true"
                                    data-search-placeholder="Search engineer"
                                    multiple
                                >
                                    <?php $__currentLoopData = $groupedEngineerOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teamLabel => $groupedOptions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <optgroup label="<?php echo e($teamLabel); ?>">
                                            <?php $__currentLoopData = $groupedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option->id); ?>" <?php if(in_array((string) $option->id, $selectedAssignedEngineerIds, true)): echo 'selected'; endif; ?>>
                                                    <?php echo e($option->name); ?>

                                                    <?php if($option->department): ?>
                                                        - <?php echo e($option->department->name); ?>

                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </optgroup>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="form-text">Bisa pilih lebih dari satu engineer. Daftar dikelompokkan per master Engineer Team agar tidak bergantung pada shift harian.</div>
                                <?php $__errorArgs = ['assigned_engineer_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <?php $__errorArgs = ['assigned_engineer_ids.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6">
                                <label for="<?php echo e($formId); ?>-assigned_team_name" class="form-label">Team</label>
                                <input
                                    type="text"
                                    id="<?php echo e($formId); ?>-assigned_team_name"
                                    name="assigned_team_name"
                                    class="form-control <?php $__errorArgs = ['assigned_team_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('assigned_team_name')); ?>"
                                    placeholder="Ops / Field Team"
                                    list="<?php echo e($formId); ?>-engineer-team-options"
                                >
                                <datalist id="<?php echo e($formId); ?>-engineer-team-options">
                                    <?php $__currentLoopData = $engineerTeamNameOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teamName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teamName); ?>"></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </datalist>
                                <?php $__errorArgs = ['assigned_team_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6">
                                <label for="<?php echo e($formId); ?>-assignment_notes" class="form-label">Assignment Notes</label>
                                <input
                                    type="text"
                                    id="<?php echo e($formId); ?>-assignment_notes"
                                    name="assignment_notes"
                                    class="form-control <?php $__errorArgs = ['assignment_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('assignment_notes')); ?>"
                                    placeholder="Instruksi singkat untuk engineer"
                                >
                                <?php $__errorArgs = ['assignment_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">
                            Assignment engineer dilakukan oleh supervisor atau admin setelah ticket dibuat.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 pt-1">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary d-none" data-step-action="prev">Back</button>
                        <button type="button" class="btn btn-primary" data-step-action="next">Continue</button>
                        <button type="submit" class="btn btn-success d-none" data-step-action="submit">Create Ticket</button>
                    </div>
                    <?php if($isModal): ?>
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                    <?php else: ?>
                        <a href="<?php echo e(route('tickets.index')); ?>" class="btn btn-outline-light">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
<?php /**PATH /Users/muhamadsobirin/Documents/cxts/resources/views/modules/tickets/tickets/partials/create-form-fields.blade.php ENDPATH**/ ?>