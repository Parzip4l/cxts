/**
* Theme: Taplox- Responsive Bootstrap 5 Admin Dashboard
* Author: Stackbros
* Module/App: Main Js
*/

// Components
import bootstrap from 'bootstrap/dist/js/bootstrap'
window.bootstrap = bootstrap;
import 'iconify-icon';
import 'simplebar/dist/simplebar'
import Choices from 'choices.js';

class Components {
    initBootstrapComponents() {

        // Popovers
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

        // Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

        // offcanvas
        const offcanvasElementList = document.querySelectorAll('.offcanvas')
        const offcanvasList = [...offcanvasElementList].map(offcanvasEl => new bootstrap.Offcanvas(offcanvasEl))

        //Toasts
        var toastPlacement = document.getElementById("toastPlacement");
        if (toastPlacement) {
            document.getElementById("selectToastPlacement").addEventListener("change", function () {
                if (!toastPlacement.dataset.originalClass) {
                    toastPlacement.dataset.originalClass = toastPlacement.className;
                }
                toastPlacement.className = toastPlacement.dataset.originalClass + " " + this.value;
            });
        }

        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
        var toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl)
        })


        const alertTrigger = document.getElementById('liveAlertBtn')
        if (alertTrigger) {
            alertTrigger.addEventListener('click', () => {
                alert('Nice, you triggered this alert message!', 'success')
            })
        }

    }

    initfullScreenListener() {
        var fullScreenBtn = document.querySelector('[data-toggle="fullscreen"]');

        if (fullScreenBtn) {
            fullScreenBtn.addEventListener('click', function (e) {
                e.preventDefault();
                document.body.classList.toggle('fullscreen-enable')
                if (!document.fullscreenElement && /* alternative standard method */ !document.mozFullScreenElement && !document.webkitFullscreenElement) {
                    // current working methods
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen();
                    } else if (document.documentElement.mozRequestFullScreen) {
                        document.documentElement.mozRequestFullScreen();
                    } else if (document.documentElement.webkitRequestFullscreen) {
                        document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                } else {
                    if (document.cancelFullScreen) {
                        document.cancelFullScreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitCancelFullScreen) {
                        document.webkitCancelFullScreen();
                    }
                }
            });
        }
    }

    // Counter Number
    initCounter() {
        var counter = document.querySelectorAll(".counter-value");
        var speed = 250; // The lower the slower
        counter &&
            counter.forEach(function (counter_value) {
                function updateCount() {
                    var target = +counter_value.getAttribute("data-target");
                    var count = +counter_value.innerText;
                    var inc = target / speed;
                    if (inc < 1) {
                        inc = 1;
                    }
                    // Check if target is reached
                    if (count < target) {
                        // Add inc to count and output in counter_value
                        counter_value.innerText = (count + inc).toFixed(0);
                        // Call function every ms
                        setTimeout(updateCount, 1);
                    } else {
                        counter_value.innerText = numberWithCommas(target);
                    }
                    numberWithCommas(counter_value.innerText);
                }
                updateCount();
            });

        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    }

    init() {
        this.initBootstrapComponents();
        this.initfullScreenListener();
        this.initCounter();
    }
}

// Form Validation ( Bootstrap )
class FormValidation {
    initFormValidation() {
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        // Loop over them and prevent submission
        document.querySelectorAll('.needs-validation').forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
    }

    init() {
        this.initFormValidation();
    }
}

class SearchableSelects {
    init(root = document) {
        root.querySelectorAll('select[data-searchable-select]').forEach((select) => {
            this.initSelect(select);
        });
    }

    initSelect(select) {
        if (!(select instanceof HTMLSelectElement)) {
            return;
        }

        if (select.dataset.choicesInitialized === 'true') {
            return;
        }

        Array.from(select.options).forEach((option) => {
            const normalizedLabel = option.textContent.replace(/\s+/g, ' ').trim();
            option.textContent = normalizedLabel;
            option.label = normalizedLabel;
        });

        const optionCount = Array.from(select.options).filter((option) => option.value !== '').length;
        const minimumOptions = Number.parseInt(select.dataset.searchMinimumOptions || '8', 10);

        if (optionCount < minimumOptions && select.dataset.forceSearchableSelect !== 'true') {
            return;
        }

        const placeholder = select.dataset.searchPlaceholder || 'Search...';

        const choices = new Choices(select, {
            allowHTML: false,
            itemSelectText: '',
            shouldSort: false,
            searchEnabled: true,
            searchPlaceholderValue: placeholder,
            noResultsText: 'No results found',
            noChoicesText: 'No options available',
            placeholder: true,
            removeItemButton: select.multiple,
            searchResultLimit: 20,
            maxItemCount: select.multiple ? -1 : 1,
            ...this.resolveCustomConfig(select),
        });

        select.dataset.choicesInitialized = 'true';
        select._choices = choices;

        if (select.hasAttribute('data-engineer-picker')) {
            choices.containerOuter.element.dataset.engineerPicker = 'true';
            this.enhanceEngineerPicker(select, choices);
        }

        if (select.classList.contains('is-invalid')) {
            choices.containerOuter.element.classList.add('is-invalid');
        }
    }

    resolveCustomConfig(select) {
        if (!select.hasAttribute('data-engineer-picker')) {
            return {};
        }

        return {
            allowHTML: true,
            callbackOnCreateTemplates: (template) => ({
                item: (classNames, data) => {
                    if (data.placeholder) {
                        return template(`
                            <div class="${classNames.item} ${classNames.itemSelectable}" data-item data-id="${data.id}" data-value="${this.escapeAttribute(data.value)}" aria-selected="true">
                                ${this.escapeHtml(data.label)}
                            </div>
                        `);
                    }

                    const props = this.normalizeCustomProperties(data.customProperties);

                    return template(`
                        <div class="${classNames.item} ${classNames.itemSelectable} engineer-picker-item" data-item data-id="${data.id}" data-value="${this.escapeAttribute(data.value)}" aria-selected="true">
                            <div class="engineer-picker-item__title">${this.escapeHtml(data.label)}</div>
                            <div class="engineer-picker-item__meta">
                                <span class="engineer-picker-badge engineer-picker-badge--${this.escapeAttribute(props.availabilityStatusClass)}">${this.escapeHtml(props.availabilityLabel)}</span>
                                <span class="engineer-picker-badge engineer-picker-badge--score-${this.escapeAttribute(props.scoreClass)}">Score ${this.escapeHtml(String(props.score))}</span>
                            </div>
                        </div>
                    `);
                },
                choice: (classNames, data) => {
                    const props = this.normalizeCustomProperties(data.customProperties);
                    const disabledClass = data.disabled ? classNames.itemDisabled : classNames.itemSelectable;

                    return template(`
                        <div class="${classNames.item} ${classNames.itemChoice} ${disabledClass} engineer-picker-choice" data-choice ${data.disabled ? 'data-choice-disabled aria-disabled="true"' : 'data-choice-selectable'} data-id="${data.id}" data-value="${this.escapeAttribute(data.value)}" ${data.groupId > 0 ? `role="treeitem"` : 'role="option"'}>
                            <div class="engineer-picker-choice__header">
                                <span class="engineer-picker-choice__name">${this.escapeHtml(data.label)}</span>
                                ${data.placeholder ? '' : `
                                    <div class="engineer-picker-choice__badges">
                                        <span class="engineer-picker-badge engineer-picker-badge--${this.escapeAttribute(props.availabilityStatusClass)}">${this.escapeHtml(props.availabilityLabel)}</span>
                                        <span class="engineer-picker-badge engineer-picker-badge--score-${this.escapeAttribute(props.scoreClass)}">Score ${this.escapeHtml(String(props.score))}</span>
                                    </div>
                                `}
                            </div>
                            ${data.placeholder ? '' : `
                                <div class="engineer-picker-choice__meta">
                                    <span>${this.escapeHtml(props.departmentName)}</span>
                                    <span>${this.escapeHtml(props.teamLabel)}</span>
                                    <span>${this.escapeHtml(String(props.workloadOpenTickets))} open ticket(s)</span>
                                </div>
                            `}
                        </div>
                    `);
                },
            }),
        };
    }

    normalizeCustomProperties(customProperties) {
        let props = customProperties || {};
        if (typeof customProperties === 'string') {
            try {
                props = JSON.parse(customProperties || '{}');
            } catch (_error) {
                props = {};
            }
        }
        const score = Number.parseInt(props.recommendation_score ?? 0, 10) || 0;
        const availabilityStatus = props.availability_status || 'unknown';
        const workloadStatus = props.workload_status || 'light';

        return {
            departmentName: props.department_name || 'No department',
            teamLabel: props.team_label || 'No team/shift',
            availabilityLabel: props.availability_label || 'Unknown',
            workloadLabel: props.workload_label || 'Light',
            workloadOpenTickets: Number.parseInt(props.workload_open_tickets ?? 0, 10) || 0,
            score,
            availabilityStatusClass: availabilityStatus === 'available'
                ? 'available'
                : (availabilityStatus === 'unavailable' ? 'unavailable' : 'unknown'),
            workloadStatusClass: workloadStatus === 'busy'
                ? 'busy'
                : (workloadStatus === 'moderate' ? 'moderate' : 'light'),
            scoreClass: score >= 80 ? 'high' : (score >= 55 ? 'medium' : 'low'),
        };
    }

    enhanceEngineerPicker(select, choices) {
        const container = choices.containerOuter.element;
        const render = () => {
            this.decorateEngineerChoiceItems(select, container);
            this.decorateEngineerSelectedItem(select, container);
        };

        render();

        container.addEventListener('showDropdown', render);
        container.addEventListener('search', render);
        container.addEventListener('choice', render);
        container.addEventListener('change', render);

        const observer = new MutationObserver(() => render());
        observer.observe(container, { childList: true, subtree: true });
    }

    decorateEngineerChoiceItems(select, container) {
        container.querySelectorAll('.choices__list--dropdown .choices__item--choice, .choices__list[aria-expanded] .choices__item--choice').forEach((choice) => {
            const value = choice.dataset.value;
            if (!value || choice.dataset.engineerEnhanced === 'true') {
                return;
            }

            const option = Array.from(select.options).find((item) => item.value === value);
            if (!option) {
                return;
            }

            const label = option.textContent.replace(/\s+/g, ' ').trim();
            const props = this.normalizeCustomProperties(option.dataset.customProperties);

            choice.innerHTML = `
                <div class="engineer-picker-choice__header">
                    <span class="engineer-picker-choice__name">${this.escapeHtml(label)}</span>
                    <div class="engineer-picker-choice__badges">
                        <span class="engineer-picker-badge engineer-picker-badge--${this.escapeAttribute(props.availabilityStatusClass)}">${this.escapeHtml(props.availabilityLabel)}</span>
                        <span class="engineer-picker-badge engineer-picker-badge--workload-${this.escapeAttribute(props.workloadStatusClass)}">${this.escapeHtml(props.workloadLabel)}</span>
                        <span class="engineer-picker-badge engineer-picker-badge--score-${this.escapeAttribute(props.scoreClass)}">Score ${this.escapeHtml(String(props.score))}</span>
                    </div>
                </div>
                <div class="engineer-picker-choice__meta">
                    <span>${this.escapeHtml(props.departmentName)}</span>
                    <span>${this.escapeHtml(props.teamLabel)}</span>
                    <span>${this.escapeHtml(String(props.workloadOpenTickets))} open ticket(s)</span>
                </div>
            `;
            choice.classList.add('engineer-picker-choice');
            choice.dataset.engineerEnhanced = 'true';
        });
    }

    decorateEngineerSelectedItem(select, container) {
        const selectedItem = container.querySelector('.choices__list--single .choices__item');
        if (!selectedItem || selectedItem.classList.contains('choices__placeholder')) {
            return;
        }

        const value = select.value;
        const option = Array.from(select.options).find((item) => item.value === value);
        if (!option) {
            return;
        }

        const label = option.textContent.replace(/\s+/g, ' ').trim();
        const props = this.normalizeCustomProperties(option.dataset.customProperties);

        selectedItem.innerHTML = `
            <div class="engineer-picker-item">
                <div class="engineer-picker-item__title">${this.escapeHtml(label)}</div>
                <div class="engineer-picker-item__meta">
                    <span class="engineer-picker-badge engineer-picker-badge--${this.escapeAttribute(props.availabilityStatusClass)}">${this.escapeHtml(props.availabilityLabel)}</span>
                    <span class="engineer-picker-badge engineer-picker-badge--workload-${this.escapeAttribute(props.workloadStatusClass)}">${this.escapeHtml(props.workloadLabel)}</span>
                    <span class="engineer-picker-badge engineer-picker-badge--score-${this.escapeAttribute(props.scoreClass)}">Score ${this.escapeHtml(String(props.score))}</span>
                </div>
            </div>
        `;
    }

    escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    escapeAttribute(value) {
        return this.escapeHtml(value).replaceAll('`', '&#096;');
    }
}

document.addEventListener('DOMContentLoaded', function (e) {
    new Components().init();
    new FormValidation().init();
    new SearchableSelects().init();
});


// Theme Layout
class ThemeLayout {

    constructor() {
        this.html = document.getElementsByTagName('html')[0]
        this.config = {};
        this.defaultConfig = window.config;
    }

    // Main Nav
    initVerticalMenu() {
        const navCollapse = document.querySelectorAll('.navbar-nav li .collapse');
        const navToggle = document.querySelectorAll(".navbar-nav li [data-bs-toggle='collapse']");

        navToggle.forEach(toggle => {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
            });
        });

        // open one menu at a time only (Auto Close Menu)
        navCollapse.forEach(collapse => {
            collapse.addEventListener('show.bs.collapse', function (event) {
                const parent = event.target.closest('.collapse.show');
                document.querySelectorAll('.navbar-nav .collapse.show').forEach(element => {
                    if (element !== event.target && element !== parent) {
                        const collapseInstance = new bootstrap.Collapse(element);
                        collapseInstance.hide();
                    }
                });
            });
        });


        if (document.querySelector(".navbar-nav")) {
            // Activate the menu in left side bar based on url
            document.querySelectorAll(".navbar-nav a").forEach(function (link) {
                var pageUrl = window.location.href.split(/[?#]/)[0];

                if (link.href === pageUrl) {
                    link.classList.add("active");
                    link.parentNode.classList.add("active");

                    let parentCollapseDiv = link.closest(".collapse");
                    while (parentCollapseDiv) {
                        parentCollapseDiv.classList.add("show");
                        parentCollapseDiv.parentElement.children[0].classList.add("active");
                        parentCollapseDiv.parentElement.children[0].setAttribute("aria-expanded", "true");
                        parentCollapseDiv = parentCollapseDiv.parentElement.closest(".collapse");
                    }
                }
            });

            setTimeout(function () {
                var activatedItem = document.querySelector('.nav-item li a.active');

                if (activatedItem != null) {
                    var simplebarContent = document.querySelector('.app-sidebar .simplebar-content-wrapper');
                    var offset = activatedItem.offsetTop - 300;
                    if (simplebarContent && offset > 100) {
                        scrollTo(simplebarContent, offset, 600);
                    }
                }
            }, 200);

            // scrollTo (Left Side Bar Active Menu)
            function easeInOutQuad(t, b, c, d) {
                t /= d / 2;
                if (t < 1) return c / 2 * t * t + b;
                t--;
                return -c / 2 * (t * (t - 2) - 1) + b;
            }

            function scrollTo(element, to, duration) {
                var start = element.scrollTop, change = to - start, currentTime = 0, increment = 20;
                var animateScroll = function () {
                    currentTime += increment;
                    var val = easeInOutQuad(currentTime, start, change, duration);
                    element.scrollTop = val;
                    if (currentTime < duration) {
                        setTimeout(animateScroll, increment);
                    }
                };
                animateScroll();
            }
        }
    }

    initConfig() {
        this.defaultConfig = JSON.parse(JSON.stringify(window.defaultConfig));
        this.config = JSON.parse(JSON.stringify(window.config));
        this.setSwitchFromConfig();
    }

    changeMenuColor(color) {
        this.config.menu.color = color;
        this.html.setAttribute('data-sidebar-color', color);
        this.setSwitchFromConfig();
    }

    changeMenuSize(size, save = true) {
        this.html.setAttribute('data-sidebar-size', size);
        if (save) {
            this.config.menu.size = size;
            this.setSwitchFromConfig();
        }
    }

    changeThemeMode(color) {
        this.config.theme = color;
        this.html.setAttribute('data-bs-theme', color);
        this.setSwitchFromConfig();
    }

    changeTopbarColor(color) {
        this.config.topbar.color = color;
        this.html.setAttribute('data-topbar-color', color);
        this.setSwitchFromConfig();
    }

    resetTheme() {
        this.config = JSON.parse(JSON.stringify(window.defaultConfig));
        this.changeMenuColor(this.config.menu.color);
        this.changeMenuSize(this.config.menu.size);
        this.changeThemeMode(this.config.theme);
        this.changeTopbarColor(this.config.topbar.color);
        this._adjustLayout();
    }

    initSwitchListener() {
        var self = this;
        document.querySelectorAll('input[name=data-sidebar-color]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeMenuColor(element.value);
            })
        });

        document.querySelectorAll('input[name=data-sidebar-size]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeMenuSize(element.value);
            })
        });

        document.querySelectorAll('input[name=data-bs-theme]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeThemeMode(element.value);
            })
        });

        document.querySelectorAll('input[name=data-topbar-color]').forEach(function (element) {
            element.addEventListener('change', function (e) {
                self.changeTopbarColor(element.value);
            })
        });

        // Topbar Light Dark Button
        var themeColorToggle = document.getElementById('light-dark-mode');
        if (themeColorToggle) {
            themeColorToggle.addEventListener('click', function (e) {
                if (self.config.theme === 'light') {
                    self.changeThemeMode('dark');
                } else {
                    self.changeThemeMode('light');
                }
            });
        }

        var resetBtn = document.querySelector('#reset-layout')
        if (resetBtn) {
            resetBtn.addEventListener('click', function (e) {
                self.resetTheme();
            });
        }

        var menuToggleBtn = document.querySelector('.button-toggle-menu');
        if (menuToggleBtn) {
            menuToggleBtn.addEventListener('click', function () {
                var configSize = self.config.menu.size;
                var size = self.html.getAttribute('data-sidebar-size', configSize);

                if (size !== 'hidden') {
                    if (size === 'condensed') {
                        self.changeMenuSize(configSize == 'condensed' ? 'default' : configSize, false);
                    } else {
                        self.changeMenuSize('condensed', false);
                    }
                } else {
                    self.showBackdrop();
                }

                self.html.classList.toggle('sidebar-enable');
            });
        }
    }

    showBackdrop() {
        const backdrop = document.createElement('div');
        backdrop.classList = 'offcanvas-backdrop fade show';
        document.body.appendChild(backdrop);
        document.body.style.overflow = "hidden";
        if (window.innerWidth > 1040) {
            document.body.style.paddingRight = "15px";
        }
        const self = this
        backdrop.addEventListener('click', function (e) {
            self.html.classList.remove('sidebar-enable');
            document.body.removeChild(backdrop);
            document.body.style.overflow = null;
            document.body.style.paddingRight = null;
        })
    }

    initWindowSize() {
        var self = this;
        window.addEventListener('resize', function (e) {
            self._adjustLayout();
        })
    }

    _adjustLayout() {
        var self = this;

        if (window.innerWidth <= 1140) {
            self.changeMenuSize('hidden', false);
        } else {
            self.changeMenuSize(self.config.menu.size);
        }
    }

    setSwitchFromConfig() {

        sessionStorage.setItem('__TAPLOX_CONFIG__', JSON.stringify(this.config));

        document.querySelectorAll('.settings-bar input[type=radio]').forEach(function (checkbox) {
            checkbox.checked = false;
        })

        var config = this.config;
        if (config) {
            var layoutColorSwitch = document.querySelector('input[type=radio][name=data-bs-theme][value=' + config.theme + ']');
            var topbarColorSwitch = document.querySelector('input[type=radio][name=data-topbar-color][value=' + config.topbar.color + ']');
            var menuSizeSwitch = document.querySelector('input[type=radio][name=data-sidebar-size][value=' + config.menu.size + ']');
            var menuColorSwitch = document.querySelector('input[type=radio][name=data-sidebar-color][value=' + config.menu.color + ']');

            if (layoutColorSwitch) layoutColorSwitch.checked = true;
            if (topbarColorSwitch) topbarColorSwitch.checked = true;
            if (menuSizeSwitch) menuSizeSwitch.checked = true;
            if (menuColorSwitch) menuColorSwitch.checked = true;
        }
    }

    init() {
        this.initVerticalMenu();
        this.initConfig();
        this.initSwitchListener();
        this.initWindowSize();
        this._adjustLayout();
        this.setSwitchFromConfig();
    }
}

new ThemeLayout().init();
