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
<main id="tableApp" class="shell">
    <section class="workspace">
        <header class="topbar">
            <div>
                <p class="eyebrow">Data entry and database preview</p>
                <h1>Products Table</h1>
            </div>
            <nav class="nav-actions">
                <a class="secondary link-button" href="${pageContext.request.contextPath}/app">Card view</a>
                <button class="secondary" @click="loadProducts">Refresh table</button>
            </nav>
        </header>

        <section class="panel" v-if="notice">
            <p>{{ notice }}</p>
        </section>

        <section class="table-layout">
            <form class="editor" @submit.prevent="submitProduct">
                <h2>Send product data</h2>
                <label>
                    SKU
                    <input v-model.trim="form.sku" placeholder="SKU-2001" required>
                </label>
                <label>
                    Product name
                    <input v-model.trim="form.name" placeholder="Mechanical mouse" required>
                </label>
                <label>
                    Category
                    <input v-model.trim="form.category" placeholder="Hardware" required>
                </label>
                <div class="fields">
                    <label>
                        Price
                        <input v-model.number="form.price" type="number" min="0" step="0.01" required>
                    </label>
                    <label>
                        Stock
                        <input v-model.number="form.stock" type="number" min="0" step="1" required>
                    </label>
                </div>
                <label class="toggle">
                    <input v-model="form.active" type="checkbox">
                    Available
                </label>
                <div class="actions">
                    <button class="primary" type="submit">Send to MongoDB</button>
                    <button class="secondary" type="button" @click="resetForm">Clear</button>
                </div>
            </form>

            <section class="list">
                <div class="list-header">
                    <h2>MongoDB products</h2>
                    <span class="count">{{ products.length }} records</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="!products.length && !loading">
                            <td colspan="7" class="empty-cell">No products found.</td>
                        </tr>
                        <tr v-for="product in products" :key="product.id">
                            <td>{{ product.sku }}</td>
                            <td>{{ product.name }}</td>
                            <td>{{ product.category }}</td>
                            <td v-text="'$' + Number(product.price).toFixed(2)"></td>
                            <td>{{ product.stock }}</td>
                            <td>
                                <span :class="product.active ? 'status active' : 'status'">
                                    {{ product.active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ formatDate(product.createdAt) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </section>
</main>
<script>
    window.APP_CONTEXT = '${pageContext.request.contextPath}';
</script>
<script src="${pageContext.request.contextPath}/assets/table.js"></script>
</body>
</html>
