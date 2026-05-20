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
        formatDate(value) {
            if (!value) {
                return '-';
            }
            return new Date(value).toLocaleString();
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
