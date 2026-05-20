const { createApp } = Vue;

const emptyForm = () => ({
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
            form: emptyForm(),
            editingId: null,
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
        startCreate() {
            this.resetForm();
        },
        editProduct(product) {
            this.editingId = product.id;
            this.form = {
                sku: product.sku,
                name: product.name,
                category: product.category,
                price: Number(product.price),
                stock: product.stock,
                active: product.active
            };
        },
        async saveProduct() {
            const url = this.editingId
                ? `${window.APP_CONTEXT}/api/products/${this.editingId}`
                : `${window.APP_CONTEXT}/api/products`;
            const method = this.editingId ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.form)
            });

            await this.handleResponse(response, async () => {
                this.notice = this.editingId ? 'Product updated successfully.' : 'Product created successfully.';
                this.resetForm();
                await this.loadProducts();
            });
        },
        async deleteProduct(id) {
            const response = await fetch(`${window.APP_CONTEXT}/api/products/${id}`, { method: 'DELETE' });
            await this.handleResponse(response, async () => {
                this.notice = 'Product deleted successfully.';
                await this.loadProducts();
            });
        },
        resetForm() {
            this.form = emptyForm();
            this.editingId = null;
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
}).mount('#app');
