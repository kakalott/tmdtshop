import './bootstrap';
import './chat';

document.addEventListener('DOMContentLoaded', () => {
    const chatShortcut = document.getElementById('brave-chat-shortcut');
    const chatToggle = document.getElementById('chat-toggle');

    chatShortcut?.addEventListener('click', () => {
        chatToggle?.click();
    });

    const menuItems = document.querySelectorAll('.brave-mega__item[data-category-panel]');
    const menuProducts = document.querySelectorAll('.brave-mini-product[data-product-category]');

    menuItems.forEach((item) => {
        item.addEventListener('mouseenter', () => {
            const panel = item.dataset.categoryPanel;

            document.querySelector('.brave-mega__item.active')?.classList.remove('active');
            item.classList.add('active');

            menuProducts.forEach((product) => {
                product.classList.toggle('is-hidden', panel !== 'all' && product.dataset.productCategory !== panel);
            });
        });

        item.addEventListener('focus', () => {
            item.dispatchEvent(new Event('mouseenter'));
        });
    });

    document.querySelectorAll('[data-banner-link-builder]').forEach((builder) => {
        const form = builder.closest('form');
        const linkInput = form?.querySelector('[data-banner-link-input]');
        const preview = form?.querySelector('[data-banner-link-preview]');
        const typeSelect = builder.querySelector('[data-link-type]');
        const panels = builder.querySelectorAll('[data-link-panel]');

        if (!linkInput || !typeSelect) {
            return;
        }

        const updatePreview = () => {
            if (preview) {
                preview.textContent = linkInput.value || 'Chưa gắn link';
            }
        };

        const showPanel = (type) => {
            panels.forEach((panel) => {
                panel.classList.toggle('d-none', panel.dataset.linkPanel !== type);
            });
        };

        const setValueFromType = () => {
            const type = typeSelect.value;
            const activeValue = builder.querySelector(`[data-link-panel="${type}"] [data-link-value]`);

            showPanel(type);
            linkInput.value = activeValue?.value || '';
            updatePreview();
        };

        const initializeFromExistingValue = () => {
            const currentValue = linkInput.value;

            if (!currentValue) {
                showPanel('');
                updatePreview();
                return;
            }

            const matchingOption = Array.from(builder.querySelectorAll('[data-link-value] option')).find((option) => option.value === currentValue);

            if (matchingOption) {
                const panel = matchingOption.closest('[data-link-panel]');

                typeSelect.value = panel.dataset.linkPanel;
                matchingOption.selected = true;
                showPanel(typeSelect.value);
            } else {
                const customInput = builder.querySelector('[data-link-panel="custom"] [data-link-value]');

                typeSelect.value = 'custom';
                customInput.value = currentValue;
                showPanel('custom');
            }

            updatePreview();
        };

        typeSelect.addEventListener('change', setValueFromType);

        builder.querySelectorAll('[data-link-value]').forEach((input) => {
            input.addEventListener('input', setValueFromType);
            input.addEventListener('change', setValueFromType);
        });

        initializeFromExistingValue();
    });
});
