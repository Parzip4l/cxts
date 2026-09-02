<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('[data-ticket-create-form]');

        forms.forEach((form) => {
            if (!form || form.dataset.ticketCreateInitialized === 'true') {
                return;
            }

            form.dataset.ticketCreateInitialized = 'true';

            const field = (suffix) => document.getElementById(`${form.id}-${suffix}`);

            const categorySelect = field('ticket_category_id');
            const processTypeSelect = field('process_type');
            const subcategorySelect = field('ticket_subcategory_id');
            const subcategoryWrapper = form.querySelector('[data-subcategory-wrapper]');
            const detailSubcategorySelect = field('ticket_detail_subcategory_id');
            const detailSubcategoryWrapper = form.querySelector('[data-detail-subcategory-wrapper]');
            const contextInputs = form.querySelectorAll('input[name="context_mode"]');
            const contextPanels = form.querySelectorAll('[data-context-panel]');
            const processScopedFields = form.querySelectorAll('[data-process-scope]');
            const serviceSelect = field('service_id');
            const requestFormFields = field('request-form-fields');
            const assetSelect = field('asset_id');
            const sharedLocationInput = field('asset_location_id');
            const locationAssetModeSelect = field('asset_location_id_asset_mode');
            const locationModeSelect = field('asset_location_id_location_mode');
            const smartHint = field('ticket-context-smart-hint');
            const stepPanels = Array.from(form.querySelectorAll('[data-step-panel]'));
            const stepTriggers = Array.from(form.querySelectorAll('[data-step-trigger]'));
            const prevButton = form.querySelector('[data-step-action="prev"]');
            const nextButton = form.querySelector('[data-step-action="next"]');
            const submitButton = form.querySelector('[data-step-action="submit"]');
            const maxStep = stepPanels.length;
            let currentStep = Number(form.dataset.initialStep || 1);

            const toggleSubcategory = () => {
                if (!categorySelect || !subcategorySelect) {
                    return;
                }

                const selectedCategoryId = categorySelect.value;
                let hasVisibleSubcategory = false;

                Array.from(subcategorySelect.options).forEach((option, index) => {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    const categoryId = option.getAttribute('data-category-id');
                    const visible = selectedCategoryId === '' || categoryId === selectedCategoryId;
                    option.hidden = !visible;
                    hasVisibleSubcategory = hasVisibleSubcategory || (selectedCategoryId !== '' && visible);

                    if (!visible && option.selected) {
                        option.selected = false;
                    }
                });

                if (subcategoryWrapper) {
                    subcategoryWrapper.classList.toggle('d-none', selectedCategoryId === '' || !hasVisibleSubcategory);
                }

                if (selectedCategoryId === '') {
                    subcategorySelect.value = '';
                    if (subcategorySelect._choices) {
                        subcategorySelect._choices.removeActiveItems();
                    }
                }

                if (!detailSubcategorySelect) {
                    return;
                }

                const selectedSubcategoryId = subcategorySelect.value;
                let hasVisibleDetailSubcategory = false;

                Array.from(detailSubcategorySelect.options).forEach((option, index) => {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    const parentSubcategoryId = option.getAttribute('data-subcategory-id');
                    const visible = selectedSubcategoryId !== '' && parentSubcategoryId === selectedSubcategoryId;
                    option.hidden = !visible;
                    hasVisibleDetailSubcategory = hasVisibleDetailSubcategory || visible;

                    if (!visible && option.selected) {
                        option.selected = false;
                    }
                });

                if (detailSubcategoryWrapper) {
                    detailSubcategoryWrapper.classList.toggle('d-none', selectedSubcategoryId === '' || !hasVisibleDetailSubcategory);
                }

                if (selectedSubcategoryId === '') {
                    detailSubcategorySelect.value = '';
                    if (detailSubcategorySelect._choices) {
                        detailSubcategorySelect._choices.removeActiveItems();
                    }
                }
            };

            const syncChoicesSelect = (select, value) => {
                if (!select) {
                    return;
                }

                select.value = value || '';

                if (select._choices) {
                    select._choices.removeActiveItems();
                    if (value) {
                        select._choices.setChoiceByValue(String(value));
                    }
                }
            };

            const clearInactiveContext = (mode) => {
                if (mode !== 'service') {
                    syncChoicesSelect(serviceSelect, '');
                }

                if (mode !== 'asset') {
                    syncChoicesSelect(assetSelect, '');
                }

                if (mode === 'none' || mode === 'service') {
                    if (sharedLocationInput) {
                        sharedLocationInput.value = '';
                    }
                    syncChoicesSelect(locationAssetModeSelect, '');
                    syncChoicesSelect(locationModeSelect, '');
                }

                if (mode === 'asset') {
                    syncChoicesSelect(locationModeSelect, '');
                }

                if (mode === 'location') {
                    syncChoicesSelect(locationAssetModeSelect, '');
                }
            };

            const syncLocationMirror = () => {
                if (!sharedLocationInput) {
                    return;
                }

                const activeMode = form.querySelector('input[name="context_mode"]:checked')?.value || 'none';

                if (activeMode === 'asset' && locationAssetModeSelect) {
                    sharedLocationInput.value = locationAssetModeSelect.value;
                    return;
                }

                if (activeMode === 'location' && locationModeSelect) {
                    sharedLocationInput.value = locationModeSelect.value;
                    return;
                }

                sharedLocationInput.value = '';
            };

            const getOptionByValue = (select, value) => {
                if (!select || !value) {
                    return null;
                }

                return Array.from(select.options).find((option) => option.value === String(value)) ?? null;
            };

            const setSmartHint = (message) => {
                if (!smartHint) {
                    return;
                }

                smartHint.innerHTML = message || '';
                smartHint.classList.toggle('d-none', !message);
            };

            const parseServiceSchema = () => {
                const selectedServiceOption = getOptionByValue(serviceSelect, serviceSelect?.value);
                if (!selectedServiceOption?.dataset.requestFormSchema) {
                    return [];
                }

                try {
                    const schema = JSON.parse(selectedServiceOption.dataset.requestFormSchema);
                    return Array.isArray(schema) ? schema : [];
                } catch (error) {
                    return [];
                }
            };

            const renderRequestFormFields = () => {
                if (!requestFormFields) {
                    return;
                }

                const activeMode = form.querySelector('input[name="context_mode"]:checked')?.value || 'none';
                const schema = activeMode === 'service' && processTypeSelect?.value === 'service_request' && serviceSelect?.value
                    ? parseServiceSchema()
                    : [];

                requestFormFields.innerHTML = '';
                requestFormFields.classList.toggle('d-none', schema.length === 0);

                schema.forEach((fieldSchema) => {
                    const name = String(fieldSchema.name || '').trim();
                    if (!name) {
                        return;
                    }

                    const type = String(fieldSchema.type || 'text').toLowerCase();
                    const label = String(fieldSchema.label || name.replaceAll('_', ' '));
                    const wrapper = document.createElement('div');
                    wrapper.className = type === 'textarea' ? 'col-12' : 'col-md-6';

                    const labelElement = document.createElement('label');
                    labelElement.className = 'form-label text-capitalize';
                    labelElement.textContent = label;
                    wrapper.appendChild(labelElement);

                    let input;
                    if (type === 'textarea') {
                        input = document.createElement('textarea');
                        input.rows = 3;
                        input.className = 'form-control';
                    } else if (type === 'select' && Array.isArray(fieldSchema.options)) {
                        input = document.createElement('select');
                        input.className = 'form-select';
                        const blankOption = document.createElement('option');
                        blankOption.value = '';
                        blankOption.textContent = '- Select -';
                        input.appendChild(blankOption);
                        fieldSchema.options.forEach((optionValue) => {
                            const option = document.createElement('option');
                            option.value = String(optionValue);
                            option.textContent = String(optionValue);
                            input.appendChild(option);
                        });
                    } else {
                        input = document.createElement('input');
                        input.type = ['number', 'date', 'email'].includes(type) ? type : 'text';
                        input.className = 'form-control';
                    }

                    input.name = `request_form_payload[${name}]`;
                    input.required = fieldSchema.required === true;
                    wrapper.appendChild(input);
                    requestFormFields.appendChild(wrapper);
                });
            };

            const updateSmartContextHint = () => {
                const activeMode = form.querySelector('input[name="context_mode"]:checked')?.value || 'none';

                if (activeMode === 'service' && serviceSelect?.value) {
                    const selectedServiceOption = getOptionByValue(serviceSelect, serviceSelect.value);
                    const serviceDefaults = [];

                    if (processTypeSelect?.value === 'service_request') {
                        const approval = selectedServiceOption?.dataset.defaultApproval;
                        const fulfillmentTeam = selectedServiceOption?.dataset.fulfillmentTeam;

                        if (approval === '1') {
                            serviceDefaults.push('approval mengikuti Service Manager');
                        } else if (approval === '0') {
                            serviceDefaults.push('tanpa approval default');
                        }

                        if (selectedServiceOption?.dataset.defaultSlaId) {
                            serviceDefaults.push('SLA default dari katalog');
                        }

                        if (fulfillmentTeam) {
                            serviceDefaults.push(`fulfillment team <strong>${fulfillmentTeam}</strong>`);
                        }
                    }

                    const relatedAssets = Array.from(assetSelect?.options ?? [])
                        .filter((option) => option.value !== '' && option.dataset.serviceId === serviceSelect.value)
                        .map((option) => option.textContent.trim());

                    const defaultMessage = serviceDefaults.length > 0
                        ? ` Default Service Request: ${serviceDefaults.join(', ')}.`
                        : '';

                    if (relatedAssets.length > 0) {
                        setSmartHint(`Service ini terhubung ke ${relatedAssets.length} asset. Contoh terkait: <strong>${relatedAssets.slice(0, 3).join(', ')}</strong>.${defaultMessage}`);
                    } else {
                        setSmartHint(`Belum ada asset aktif yang terhubung langsung ke service ini.${defaultMessage}`);
                    }

                    return;
                }

                if (activeMode === 'asset' && assetSelect?.value) {
                    const selectedAssetOption = getOptionByValue(assetSelect, assetSelect.value);
                    const relatedServiceId = selectedAssetOption?.dataset.serviceId || '';
                    const relatedLocationId = selectedAssetOption?.dataset.locationId || '';
                    const relatedServiceName = getOptionByValue(serviceSelect, relatedServiceId)?.textContent?.trim();
                    const relatedLocationName = getOptionByValue(locationAssetModeSelect ?? locationModeSelect, relatedLocationId)?.textContent?.trim();

                    if (relatedServiceId) {
                        syncChoicesSelect(serviceSelect, relatedServiceId);
                    }

                    if (relatedLocationId && locationAssetModeSelect && !locationAssetModeSelect.value) {
                        syncChoicesSelect(locationAssetModeSelect, relatedLocationId);
                    }

                    syncLocationMirror();

                    const details = [
                        relatedServiceName ? `service <strong>${relatedServiceName}</strong>` : null,
                        relatedLocationName ? `location <strong>${relatedLocationName}</strong>` : null,
                    ].filter(Boolean);

                    setSmartHint(details.length > 0
                        ? `Asset ini terhubung ke ${details.join(' dan ')}. Field terkait sudah dibantu isi otomatis jika datanya tersedia.`
                        : 'Asset ini belum punya relasi service atau location yang lengkap di master data.');

                    return;
                }

                if (activeMode === 'location' && locationModeSelect?.value) {
                    const relatedAssets = Array.from(assetSelect?.options ?? [])
                        .filter((option) => option.value !== '' && option.dataset.locationId === locationModeSelect.value)
                        .map((option) => option.textContent.trim());

                    if (relatedAssets.length > 0) {
                        setSmartHint(`Di location ini ada ${relatedAssets.length} asset terkait. Contoh: <strong>${relatedAssets.slice(0, 3).join(', ')}</strong>.`);
                    } else {
                        setSmartHint('Belum ada asset aktif yang dipetakan ke location ini.');
                    }

                    return;
                }

                setSmartHint('');
            };

            const syncProcessScopedFields = () => {
                const processType = processTypeSelect?.value || 'incident';

                processScopedFields.forEach((wrapper) => {
                    wrapper.classList.toggle('d-none', wrapper.dataset.processScope !== processType);
                });

                updateSmartContextHint();
                renderRequestFormFields();
            };

            const syncContextPanels = () => {
                const activeMode = form.querySelector('input[name="context_mode"]:checked')?.value || 'none';

                contextPanels.forEach((panel) => {
                    panel.classList.toggle('d-none', panel.dataset.contextPanel !== activeMode);
                });

                if (serviceSelect) {
                    serviceSelect.required = activeMode === 'service';
                }

                if (assetSelect) {
                    assetSelect.required = activeMode === 'asset';
                }

                if (locationModeSelect) {
                    locationModeSelect.required = activeMode === 'location';
                }

                clearInactiveContext(activeMode);
                syncLocationMirror();
                updateSmartContextHint();
                renderRequestFormFields();
            };

            const fieldsForStep = (step) => {
                if (step === 1) {
                    return [
                        field('title'),
                        field('ticket_category_id'),
                        field('description'),
                    ].filter(Boolean);
                }

                if (step === 2) {
                    const activeMode = form.querySelector('input[name="context_mode"]:checked')?.value || 'none';
                    const fields = [];

                    if (activeMode === 'service' && serviceSelect) {
                        fields.push(serviceSelect);
                    }
                    if (activeMode === 'asset' && assetSelect) {
                        fields.push(assetSelect);
                    }
                    if (activeMode === 'location' && locationModeSelect) {
                        fields.push(locationModeSelect);
                    }

                    return fields;
                }

                return [];
            };

            const validateStep = (step) => {
                const fields = fieldsForStep(step);
                for (const currentField of fields) {
                    if (!currentField.checkValidity()) {
                        currentField.reportValidity();
                        return false;
                    }
                }

                return true;
            };

            const showStep = (step) => {
                currentStep = Math.min(Math.max(step, 1), maxStep);

                stepPanels.forEach((panel) => {
                    panel.classList.toggle('d-none', Number(panel.dataset.stepPanel) !== currentStep);
                });

                stepTriggers.forEach((trigger) => {
                    const stepNumber = Number(trigger.dataset.stepTrigger);
                    const isActive = stepNumber === currentStep;
                    trigger.classList.toggle('btn-primary', isActive);
                    trigger.classList.toggle('text-white', isActive);
                    trigger.classList.toggle('btn-outline-primary', !isActive);
                });

                prevButton?.classList.toggle('d-none', currentStep === 1);
                nextButton?.classList.toggle('d-none', currentStep === maxStep);
                submitButton?.classList.toggle('d-none', currentStep !== maxStep);
            };

            categorySelect?.addEventListener('change', toggleSubcategory);
            processTypeSelect?.addEventListener('change', syncProcessScopedFields);
            subcategorySelect?.addEventListener('change', toggleSubcategory);
            contextInputs.forEach((input) => input.addEventListener('change', syncContextPanels));
            serviceSelect?.addEventListener('change', () => {
                updateSmartContextHint();
                renderRequestFormFields();
            });
            assetSelect?.addEventListener('change', updateSmartContextHint);
            locationAssetModeSelect?.addEventListener('change', syncLocationMirror);
            locationAssetModeSelect?.addEventListener('change', updateSmartContextHint);
            locationModeSelect?.addEventListener('change', syncLocationMirror);
            locationModeSelect?.addEventListener('change', updateSmartContextHint);

            stepTriggers.forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    const targetStep = Number(trigger.dataset.stepTrigger);
                    if (targetStep > currentStep && !validateStep(currentStep)) {
                        return;
                    }
                    showStep(targetStep);
                });
            });

            nextButton?.addEventListener('click', () => {
                if (!validateStep(currentStep)) {
                    return;
                }
                showStep(currentStep + 1);
            });

            prevButton?.addEventListener('click', () => showStep(currentStep - 1));

            toggleSubcategory();
            syncProcessScopedFields();
            syncContextPanels();
            updateSmartContextHint();
            renderRequestFormFields();
            showStep(currentStep);
        });
    });
</script>
