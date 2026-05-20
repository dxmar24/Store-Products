const { createApp } = Vue;

const emptyTableForm = () => ({
    sku: '',
    name: '',
    category: '',
    price: 0,
    stock: 0,
    active: true
});

createApp({
    data() {
        return {
            products: [],
            form: emptyTableForm(),
            loading: false,
            notice: ''
        };
    },
    mounted() {
        this.loadProducts();
    },
    methods: {
        async loadProducts() {
            this.loading = true;
            this.notice = '';
            try {
                const response = await fetch(`${window.APP_CONTEXT}/api/products`);
                await this.handleResponse(response, async data => {
                    this.products = data;
                });
            } finally {
                this.loading = false;
            }
        },
        async submitProduct() {
            if (!this.validateForm()) {
                return;
            }

            const response = await fetch(`${window.APP_CONTEXT}/api/products`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.form)
            });

            await this.handleResponse(response, async () => {
                this.notice = 'Product sent and saved successfully.';
                this.resetForm();
                await this.loadProducts();
            });
        },
        resetForm() {
            this.form = emptyTableForm();
        },
        validateForm() {
            const sku = String(this.form.sku || '').trim().toUpperCase();
            const name = String(this.form.name || '').trim();
            const category = String(this.form.category || '').trim();
            const price = Number(this.form.price);
            const stock = Number(this.form.stock);

            if (!sku || !/^[A-Z0-9-]+$/.test(sku) || sku.length > 30) {
                this.notice = 'SKU is required and can only contain letters, numbers, and hyphens.';
                return false;
            }
            if (!name || name.length > 80) {
                this.notice = 'Product name is required and must be 80 characters or fewer.';
                return false;
            }
            if (!category || category.length > 50) {
                this.notice = 'Category is required and must be 50 characters or fewer.';
                return false;
            }
            if (!Number.isFinite(price) || price <= 0) {
                this.notice = 'Price must be greater than zero.';
                return false;
            }
            if (!Number.isInteger(stock) || stock <= 0) {
                this.notice = 'Quantity must be a whole number greater than zero.';
                return false;
            }

            const duplicatedSku = this.products.some(product => product.sku === sku);
            if (duplicatedSku) {
                this.notice = 'SKU already exists. Use a different SKU.';
                return false;
            }

            this.form.sku = sku;
            this.form.name = name;
            this.form.category = category;
            this.form.price = price;
            this.form.stock = stock;
            return true;
        },
        productTotal(product) {
            return Number(product.price || 0) * Number(product.stock || 0);
        },
        formatCurrency(value) {
            return '$' + Number(value || 0).toFixed(2);
        },
        formatDate(value) {
            if (!value) {
                return '-';
            }
            return new Date(value).toLocaleDateString();
        },
        async handleResponse(response, onSuccess) {
            if (response.ok) {
                const data = response.status === 204 ? null : await response.json();
                await onSuccess(data);
                return;
            }
            const error = await response.json().catch(() => ({ message: 'Request failed.' }));
            this.notice = error.message;
        }
    }
}).mount('#tableApp');
