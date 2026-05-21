const apiUrl = '/api/products';

const state = {
    products: [],
    editingId: null,
    loading: false
};

const elements = {
    form: document.querySelector('#productForm'),
    productId: document.querySelector('#productId'),
    sku: document.querySelector('#sku'),
    name: document.querySelector('#name'),
    category: document.querySelector('#category'),
    price: document.querySelector('#price'),
    stock: document.querySelector('#stock'),
    active: document.querySelector('#active'),
    rows: document.querySelector('#productRows'),
    notice: document.querySelector('#notice'),
    rowTemplate: document.querySelector('#rowTemplate'),
    refreshButton: document.querySelector('#refreshButton'),
    newButton: document.querySelector('#newButton'),
    clearButton: document.querySelector('#clearButton'),
    submitButton: document.querySelector('#submitButton'),
    formTitle: document.querySelector('#formTitle'),
    modeBadge: document.querySelector('#modeBadge'),
    countBadge: document.querySelector('#countBadge')
};

const fieldRules = {
    sku(value) {
        const normalized = value.trim().toUpperCase();
        if (!normalized) return 'SKU is required.';
        if (!/^[A-Z0-9-]+$/.test(normalized)) return 'Use only letters, numbers, and hyphens.';
        if (normalized.length > 30) return 'SKU must be 30 characters or fewer.';
        const duplicate = state.products.some(product => product.sku === normalized && product.id !== state.editingId);
        if (duplicate) return 'SKU already exists. Use a different SKU.';
        return '';
    },
    name(value) {
        const normalized = value.trim();
        if (!normalized) return 'Product name is required.';
        if (normalized.length > 80) return 'Product name must be 80 characters or fewer.';
        return '';
    },
    category(value) {
        const normalized = value.trim();
        if (!normalized) return 'Category is required.';
        if (normalized.length > 50) return 'Category must be 50 characters or fewer.';
        return '';
    },
    price(value) {
        const amount = Number(value);
        if (!Number.isFinite(amount) || amount <= 0 || amount > 999999) {
            return 'Price must be greater than zero and no more than 999999.';
        }
        return '';
    },
    stock(value) {
        const quantity = Number(value);
        if (!Number.isInteger(quantity) || quantity <= 0 || quantity > 999999) {
            return 'Quantity must be a whole number greater than zero and no more than 999999.';
        }
        return '';
    },
    active() {
        return '';
    }
};

document.addEventListener('DOMContentLoaded', () => {
    elements.form.addEventListener('submit', submitForm);
    elements.refreshButton.addEventListener('click', loadProducts);
    elements.newButton.addEventListener('click', resetForm);
    elements.clearButton.addEventListener('click', resetForm);

    for (const input of [elements.sku, elements.name, elements.category, elements.price, elements.stock]) {
        input.addEventListener('input', () => validateField(input.name));
    }

    loadProducts();
});

async function loadProducts() {
    state.loading = true;
    renderLoading();
    clearNotice();

    try {
        const payload = await request(apiUrl);
        state.products = payload.data || [];
        renderProducts();
    } catch (error) {
        showNotice(error.message || 'Unable to load products.', 'error');
        renderProducts();
    } finally {
        state.loading = false;
    }
}

async function submitForm(event) {
    event.preventDefault();
    clearNotice();

    const product = getFormProduct();
    const errors = validateForm();
    if (Object.keys(errors).length > 0) {
        showFieldErrors(errors);
        showNotice('Please check the highlighted fields.', 'error');
        return;
    }

    const url = state.editingId ? `${apiUrl}/${state.editingId}` : apiUrl;
    const method = state.editingId ? 'PUT' : 'POST';

    elements.submitButton.disabled = true;

    try {
        await request(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(product)
        });
        showNotice(state.editingId ? 'Product updated successfully.' : 'Product created successfully.', 'success');
        resetForm();
        await loadProducts();
    } catch (error) {
        if (error.errors) {
            showFieldErrors(error.errors);
        }
        showNotice(error.message || 'Unable to save product.', 'error');
    } finally {
        elements.submitButton.disabled = false;
    }
}

async function deleteProduct(product) {
    const confirmed = window.confirm(`Delete ${product.sku} - ${product.name}?`);
    if (!confirmed) {
        return;
    }

    try {
        await request(`${apiUrl}/${product.id}`, { method: 'DELETE' });
        showNotice('Product deleted successfully.', 'success');
        if (state.editingId === product.id) {
            resetForm();
        }
        await loadProducts();
    } catch (error) {
        showNotice(error.message || 'Unable to delete product.', 'error');
    }
}

function editProduct(product) {
    state.editingId = product.id;
    elements.productId.value = product.id;
    elements.sku.value = product.sku;
    elements.name.value = product.name;
    elements.category.value = product.category;
    elements.price.value = Number(product.price).toFixed(2);
    elements.stock.value = product.stock;
    elements.active.checked = Boolean(product.active);
    elements.formTitle.textContent = 'Edit product';
    elements.modeBadge.textContent = 'Editing';
    elements.submitButton.textContent = 'Update';
    clearFieldErrors();
    elements.sku.focus();
}

function resetForm() {
    state.editingId = null;
    elements.form.reset();
    elements.productId.value = '';
    elements.active.checked = true;
    elements.formTitle.textContent = 'Create product';
    elements.modeBadge.textContent = 'New';
    elements.submitButton.textContent = 'Create';
    clearFieldErrors();
}

function getFormProduct() {
    return {
        sku: elements.sku.value.trim().toUpperCase(),
        name: elements.name.value.trim(),
        category: elements.category.value.trim(),
        price: Number(elements.price.value),
        stock: Number(elements.stock.value),
        active: elements.active.checked
    };
}

function validateForm() {
    const values = {
        sku: elements.sku.value,
        name: elements.name.value,
        category: elements.category.value,
        price: elements.price.value,
        stock: elements.stock.value,
        active: elements.active.checked
    };
    const errors = {};

    for (const [field, validator] of Object.entries(fieldRules)) {
        const message = validator(values[field]);
        if (message) {
            errors[field] = message;
        }
    }

    return errors;
}

function validateField(field) {
    const input = elements[field];
    if (!input || !fieldRules[field]) {
        return;
    }

    const message = fieldRules[field](input.value);
    showFieldErrors({ [field]: message });
}

function showFieldErrors(errors) {
    for (const field of Object.keys(fieldRules)) {
        const message = errors[field];
        const input = elements[field];
        const output = document.querySelector(`[data-error-for="${field}"]`);

        if (message === undefined) {
            continue;
        }

        if (input) {
            input.classList.toggle('invalid', Boolean(message));
        }
        if (output) {
            output.textContent = message || '';
        }
    }
}

function clearFieldErrors() {
    for (const field of Object.keys(fieldRules)) {
        showFieldErrors({ [field]: '' });
    }
}

function renderLoading() {
    elements.rows.innerHTML = '<tr><td class="empty-cell" colspan="9">Loading products...</td></tr>';
}

function renderProducts() {
    elements.rows.textContent = '';
    elements.countBadge.textContent = `${state.products.length} ${state.products.length === 1 ? 'product' : 'products'}`;

    if (state.products.length === 0) {
        elements.rows.innerHTML = '<tr><td class="empty-cell" colspan="9">No products found.</td></tr>';
        return;
    }

    for (const product of state.products) {
        const row = elements.rowTemplate.content.firstElementChild.cloneNode(true);
        setCell(row, 'sku', product.sku);
        setCell(row, 'name', product.name);
        setCell(row, 'category', product.category);
        setCell(row, 'price', formatCurrency(product.price));
        setCell(row, 'stock', product.stock);
        setCell(row, 'total', formatCurrency(Number(product.price) * Number(product.stock)));
        setCell(row, 'createdAt', formatDate(product.createdAt));

        const status = row.querySelector('[data-cell="status"]');
        status.textContent = product.active ? 'Active' : 'Inactive';
        status.classList.toggle('active', product.active);

        row.querySelector('[data-action="edit"]').addEventListener('click', () => editProduct(product));
        row.querySelector('[data-action="delete"]').addEventListener('click', () => deleteProduct(product));
        elements.rows.appendChild(row);
    }
}

function setCell(row, name, value) {
    row.querySelector(`[data-cell="${name}"]`).textContent = value ?? '';
}

async function request(url, options = {}) {
    const response = await fetch(url, options);
    const payload = await response.json().catch(() => ({}));

    if (!response.ok || payload.success === false) {
        const error = new Error(payload.message || 'Request failed.');
        error.errors = payload.errors || null;
        throw error;
    }

    return payload;
}

function showNotice(message, type = 'info') {
    elements.notice.textContent = message;
    elements.notice.dataset.type = type;
    elements.notice.hidden = false;
}

function clearNotice() {
    elements.notice.textContent = '';
    elements.notice.hidden = true;
}

function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(Number(value || 0));
}

function formatDate(value) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit'
    }).format(new Date(value));
}
