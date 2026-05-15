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
            const subcategorySelect = field('ticket_subcategory_id');
            const subcategoryWrapper = form.querySelector('[data-subcategory-wrapper]');
            const detailSubcategorySelect = field('ticket_detail_subcategory_id');
            const detailSubcategoryWrapper = form.querySelector('[data-detail-subcategory-wrapper]');
            const contextInputs = form.querySelectorAll('input[name="context_mode"]');
            const contextPanels = form.querySelectorAll('[data-context-panel]');
            const serviceSelect = field('service_id');
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

            const updateSmartContextHint = () => {
                const activeMode = form.querySelector('input[name="context_mode"]:checked')?.value || 'none';

                if (activeMode === 'service' && serviceSelect?.value) {
                    const relatedAssets = Array.from(assetSelect?.options ?? [])
                        .filter((option) => option.value !== '' && option.dataset.serviceId === serviceSelect.value)
                        .map((option) => option.textContent.trim());

                    if (relatedAssets.length > 0) {
                        setSmartHint(`Service ini terhubung ke ${relatedAssets.length} asset. Contoh terkait: <strong>${relatedAssets.slice(0, 3).join(', ')}</strong>.`);
                    } else {
                        setSmartHint('Belum ada asset aktif yang terhubung langsung ke service ini.');
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
            subcategorySelect?.addEventListener('change', toggleSubcategory);
            contextInputs.forEach((input) => input.addEventListener('change', syncContextPanels));
            serviceSelect?.addEventListener('change', updateSmartContextHint);
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
            syncContextPanels();
            updateSmartContextHint();
            showStep(currentStep);
        });
    });
</script>
