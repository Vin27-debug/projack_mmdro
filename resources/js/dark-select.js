const SELECTOR = 'select:not([multiple]):not([size]):not([data-native-select])';

class DarkSelect {
    constructor(select) {
        this.select = select;
        this.activeIndex = -1;
        this.isOpen = false;
        this.typeahead = '';
        this.id = select.id || `mr-dark-select-${crypto.randomUUID()}`;
        select.id = this.id;

        this.build();
        this.bind();
        this.sync();
        this.renderOptions();
        this.observer = new MutationObserver(() => {
            this.sync();
            this.renderOptions();
        });
        this.observer.observe(select, {
            attributes: true,
            childList: true,
            subtree: true,
            attributeFilter: ['class', 'disabled', 'label', 'selected', 'value'],
        });
    }

    build() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'mr-dark-select';
        this.wrapper.dataset.open = 'false';
        this.wrapper.dataset.selectId = this.id;

        this.trigger = document.createElement('button');
        this.trigger.type = 'button';
        this.trigger.className = [
            'mr-dark-select__trigger',
            ...[...this.select.classList].filter((name) => name !== 'form-select'),
        ].join(' ');
        this.trigger.id = `${this.id}-trigger`;
        this.trigger.setAttribute('role', 'combobox');
        this.trigger.setAttribute('aria-haspopup', 'listbox');
        this.trigger.setAttribute('aria-expanded', 'false');
        this.trigger.setAttribute('aria-controls', `${this.id}-listbox`);

        const labels = [...(this.select.labels || [])];
        if (labels.length) {
            labels.forEach((label, index) => {
                if (!label.id) label.id = `${this.id}-label-${index + 1}`;
                if (label.htmlFor === this.id) label.htmlFor = this.trigger.id;
            });
            this.trigger.setAttribute('aria-labelledby', labels.map((label) => label.id).join(' '));
        } else if (this.select.getAttribute('aria-label')) {
            this.trigger.setAttribute('aria-label', this.select.getAttribute('aria-label'));
        } else {
            this.trigger.setAttribute('aria-label', this.select.name || 'Select an option');
        }
        labels.forEach((label) => label.addEventListener('click', (event) => {
            event.preventDefault();
            this.trigger.focus();
        }));

        this.valueNode = document.createElement('span');
        this.valueNode.className = 'mr-dark-select__value';
        this.chevron = document.createElement('span');
        this.chevron.className = 'mr-dark-select__chevron';
        this.chevron.setAttribute('aria-hidden', 'true');
        this.trigger.append(this.valueNode, this.chevron);

        this.menu = document.createElement('div');
        this.menu.className = 'mr-dark-select__menu';
        this.menu.id = `${this.id}-listbox`;
        this.menu.setAttribute('role', 'listbox');
        this.menu.hidden = true;

        this.wrapper.append(this.trigger, this.menu);
        this.select.insertAdjacentElement('afterend', this.wrapper);
        this.select.classList.add('mr-dark-select__native');
        this.select.setAttribute('aria-hidden', 'true');
        this.select.tabIndex = -1;

        const describedBy = this.select.getAttribute('aria-describedby');
        const feedback = this.select.parentElement?.querySelector('.invalid-feedback');
        if (feedback && !feedback.id) feedback.id = `${this.id}-error`;
        if (describedBy || feedback?.id) {
            this.trigger.setAttribute('aria-describedby', [describedBy, feedback?.id].filter(Boolean).join(' '));
        }

        this.select.addEventListener('invalid', (event) => {
            event.preventDefault();
            this.trigger.focus();
            this.trigger.setAttribute('aria-invalid', 'true');
        });
    }

    bind() {
        this.trigger.addEventListener('click', () => {
            if (this.select.disabled) return;
            this.isOpen ? this.close() : this.open();
        });
        this.trigger.addEventListener('keydown', (event) => this.onKeydown(event));
        this.select.addEventListener('change', () => this.sync());
        this.select.addEventListener('input', () => this.sync());
        this.menu.addEventListener('click', (event) => {
            const option = event.target.closest('[data-option-index]');
            if (option && !option.disabled) this.choose(Number(option.dataset.optionIndex));
        });
        this.menu.addEventListener('mousemove', (event) => {
            const option = event.target.closest('[data-option-index]');
            if (option && !option.disabled) this.setActive(Number(option.dataset.optionIndex));
        });
        document.addEventListener('pointerdown', (event) => {
            if (!this.wrapper.contains(event.target)) this.close();
        });
        this.select.form?.addEventListener('reset', () => requestAnimationFrame(() => this.sync()));
    }

    get options() {
        return [...this.select.options];
    }

    get enabledIndexes() {
        return this.options
            .map((option, index) => ({ option, index }))
            .filter(({ option }) => !option.disabled && !option.parentElement.disabled)
            .map(({ index }) => index);
    }

    sync() {
        const selected = this.select.selectedOptions[0];
        this.valueNode.textContent = selected?.textContent.trim() || '';
        this.valueNode.dataset.placeholder = String(!selected || selected.value === '');
        this.trigger.disabled = this.select.disabled;
        this.trigger.classList.toggle('is-invalid', this.select.classList.contains('is-invalid'));
        this.trigger.setAttribute('aria-invalid', String(
            this.select.classList.contains('is-invalid') || this.select.getAttribute('aria-invalid') === 'true',
        ));
        this.trigger.setAttribute('aria-required', String(this.select.required));
        this.trigger.setAttribute('aria-disabled', String(this.select.disabled));
    }

    renderOptions() {
        this.menu.replaceChildren();
        let index = 0;

        [...this.select.children].forEach((child) => {
            if (child instanceof HTMLOptGroupElement) {
                const label = document.createElement('div');
                label.className = 'mr-dark-select__group-label';
                label.textContent = child.label;
                label.setAttribute('role', 'presentation');
                this.menu.append(label);
                [...child.children].forEach((option) => this.appendOption(option, index++, child.disabled));
            } else if (child instanceof HTMLOptionElement) {
                this.appendOption(child, index++);
            }
        });
        this.syncActiveOption();
    }

    appendOption(option, index, groupDisabled = false) {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'mr-dark-select__option';
        item.dataset.optionIndex = String(index);
        item.id = `${this.id}-option-${index}`;
        item.setAttribute('role', 'option');
        item.setAttribute('aria-selected', String(option.selected));
        item.textContent = option.label;
        item.disabled = option.disabled || groupDisabled;
        this.menu.append(item);
    }

    open() {
        if (this.select.disabled) return;
        this.isOpen = true;
        this.wrapper.dataset.open = 'true';
        this.wrapper.dataset.placement = 'bottom';
        this.menu.hidden = false;
        this.trigger.setAttribute('aria-expanded', 'true');
        const bounds = this.wrapper.getBoundingClientRect();
        const spaceBelow = window.innerHeight - bounds.bottom - 8;
        const spaceAbove = bounds.top - 8;
        if (spaceBelow < this.menu.scrollHeight && spaceAbove > spaceBelow) {
            this.wrapper.dataset.placement = 'top';
        }
        const enabled = this.enabledIndexes;
        this.activeIndex = enabled.includes(this.select.selectedIndex)
            ? this.select.selectedIndex
            : (enabled[0] ?? -1);
        this.syncActiveOption();
        this.scrollActiveIntoView();
    }

    close() {
        this.isOpen = false;
        this.wrapper.dataset.open = 'false';
        this.wrapper.dataset.placement = 'bottom';
        this.menu.hidden = true;
        this.trigger.setAttribute('aria-expanded', 'false');
        this.trigger.removeAttribute('aria-activedescendant');
    }

    choose(index) {
        const option = this.options[index];
        if (!option || option.disabled || option.parentElement.disabled) return;
        this.select.selectedIndex = index;
        this.select.dispatchEvent(new Event('input', { bubbles: true }));
        this.select.dispatchEvent(new Event('change', { bubbles: true }));
        this.trigger.removeAttribute('aria-invalid');
        this.close();
        this.trigger.focus();
    }

    setActive(index) {
        if (!this.enabledIndexes.includes(index)) return;
        this.activeIndex = index;
        this.syncActiveOption();
    }

    syncActiveOption() {
        this.menu.querySelectorAll('[data-option-index]').forEach((item) => {
            item.dataset.active = String(Number(item.dataset.optionIndex) === this.activeIndex);
            item.setAttribute('aria-selected', String(Number(item.dataset.optionIndex) === this.select.selectedIndex));
        });
        const activeId = this.activeIndex >= 0 ? `${this.id}-option-${this.activeIndex}` : null;
        if (activeId) this.trigger.setAttribute('aria-activedescendant', activeId);
        else this.trigger.removeAttribute('aria-activedescendant');
    }

    scrollActiveIntoView() {
        this.menu.querySelector(`[data-option-index="${this.activeIndex}"]`)?.scrollIntoView({ block: 'nearest' });
    }

    moveActive(direction) {
        const enabled = this.enabledIndexes;
        if (!enabled.length) return;
        const current = enabled.indexOf(this.activeIndex);
        const next = current === -1
            ? (direction > 0 ? 0 : enabled.length - 1)
            : (current + direction + enabled.length) % enabled.length;
        this.setActive(enabled[next]);
        this.scrollActiveIntoView();
    }

    onKeydown(event) {
        if (event.key === 'Tab') {
            this.close();
            return;
        }
        if (event.key === 'Escape') {
            if (this.isOpen) {
                event.preventDefault();
                this.close();
            }
            return;
        }
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (!this.isOpen) this.open();
            else this.moveActive(event.key === 'ArrowDown' ? 1 : -1);
            return;
        }
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            if (!this.isOpen) this.open();
            else this.choose(this.activeIndex);
            return;
        }
        if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
            this.search(event.key);
        }
    }

    search(character) {
        this.typeahead = `${this.typeahead}${character.toLocaleLowerCase()}`;
        clearTimeout(this.typeaheadTimer);
        this.typeaheadTimer = setTimeout(() => { this.typeahead = ''; }, 700);
        const enabled = this.enabledIndexes;
        if (!enabled.length) return;
        const start = enabled.indexOf(this.activeIndex);
        const ordered = [...enabled.slice(start + 1), ...enabled.slice(0, start + 1)];
        const match = ordered.find((index) => this.options[index].label.trim().toLocaleLowerCase().startsWith(this.typeahead));
        if (match === undefined) return;
        if (!this.isOpen) this.open();
        this.setActive(match);
        this.scrollActiveIntoView();
    }
}

export function enhanceDarkSelects(root = document) {
    root.querySelectorAll(SELECTOR).forEach((select) => {
        if (select.dataset.darkSelectEnhanced === 'true') return;
        select.dataset.darkSelectEnhanced = 'true';
        new DarkSelect(select);
    });
}