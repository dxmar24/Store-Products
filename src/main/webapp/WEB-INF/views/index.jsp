<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${pageTitle}</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/assets/styles.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/assets/table.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</head>
<body>
<main id="app" class="shell">
    <section class="workspace">
        <header class="topbar">
            <div>
                <p class="eyebrow">MongoDB Atlas MVC Application</p>
                <h1>Store Products</h1>
            </div>
            <nav class="nav-actions">
                <a class="secondary link-button" href="${pageContext.request.contextPath}/table">Table view</a>
                <button class="primary" @click="startCreate">New product</button>
            </nav>
        </header>

        <section class="panel" v-if="notice">
            <p>{{ notice }}</p>
        </section>

        <section class="content-grid">
            <form class="editor" @submit.prevent="saveProduct">
                <h2>{{ editingId ? 'Edit product' : 'Create product' }}</h2>
                <label>
                    SKU
                    <input v-model.trim="form.sku" placeholder="SKU-1001" maxlength="30" pattern="[A-Za-z0-9-]+" title="Use letters, numbers, and hyphens only." required>
                </label>
                <label>
                    Name
                    <input v-model.trim="form.name" placeholder="Wireless keyboard" maxlength="80" required>
                </label>
                <label>
                    Category
                    <input v-model.trim="form.category" placeholder="Accessories" maxlength="50" required>
                </label>
                <div class="fields">
                    <label>
                        Price
                        <input v-model.number="form.price" type="number" min="0.01" max="999999" step="0.01" required>
                    </label>
                    <label>
                        Quantity
                        <input v-model.number="form.stock" type="number" min="1" max="999999" step="1" required>
                    </label>
                </div>
                <label class="toggle">
                    <input v-model="form.active" type="checkbox">
                    Active product
                </label>
                <div class="actions">
                    <button class="primary" type="submit">{{ editingId ? 'Update' : 'Create' }}</button>
                    <button class="secondary" type="button" @click="resetForm">Clear</button>
                </div>
            </form>

            <section class="list">
                <div class="list-header">
                    <h2>Inventory</h2>
                    <button class="secondary" @click="loadProducts">Refresh</button>
                </div>
                <div class="empty" v-if="!products.length && !loading">
                    No products yet.
                </div>
                <article class="product-card" v-for="product in products" :key="product.id">
                    <div>
                        <p class="sku">{{ product.sku }}</p>
                        <h3>{{ product.name }}</h3>
                        <p>{{ product.category }}</p>
                    </div>
                    <div class="metrics">
                        <strong>{{ formatCurrency(productTotal(product)) }}</strong>
                        <span>{{ product.stock }} quantity</span>
                        <span>{{ formatDate(product.createdAt) }}</span>
                        <span :class="product.active ? 'status active' : 'status'">
                            {{ product.active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="row-actions">
                        <button class="secondary" @click="editProduct(product)">Edit</button>
                        <button class="danger" @click="deleteProduct(product.id)">Delete</button>
                    </div>
                </article>
            </section>
        </section>
    </section>
</main>
<script>
    window.APP_CONTEXT = '${pageContext.request.contextPath}';
</script>
<script src="${pageContext.request.contextPath}/assets/app.js"></script>
</body>
</html>
