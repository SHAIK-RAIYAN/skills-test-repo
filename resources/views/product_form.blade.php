<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Skills Test</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-4 fw-bold text-primary">Inventory Management</h2>

        <div id="alert-container"></div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Add New Product</h5>
                        <form id="product-form">
                            <div class="mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price per Item</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price" min="0" step="0.01" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="submit-btn">
                                Submit Entry
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Submitted Entries</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Name</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th>Submitted At</th>
                                        <th class="text-end">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody id="product-list">
                                    </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="4" class="text-end">Grand Total:</td>
                                        <td class="text-end" id="grand-total">$0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        // Wait for standard DOM ready, utilizing modern ES6+ standards
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('product-form');
            const listContainer = document.getElementById('product-list');
            const totalContainer = document.getElementById('grand-total');
            const submitBtn = document.getElementById('submit-btn');

            // Initial fetch
            fetchProducts();

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                setLoading(true);

                try {
                    // Using Laravel's default Axios instance (handles CSRF automatically)
                    const response = await axios.post('/save-product', {
                        product_name: document.getElementById('product_name').value,
                        quantity: document.getElementById('quantity').value,
                        price: document.getElementById('price').value
                    });

                    showAlert('success', 'Product saved successfully!');
                    form.reset();
                    fetchProducts();
                } catch (error) {
                    showAlert('danger', error.response?.data?.message || 'An error occurred while saving.');
                } finally {
                    setLoading(false);
                }
            });

            async function fetchProducts() {
                try {
                    const { data } = await axios.get('/get-products');
                    renderTable(data);
                } catch (error) {
                    showAlert('danger', 'Failed to load product data.');
                }
            }

            function renderTable(products) {
                if (products.length === 0) {
                    listContainer.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No data submitted yet</td></tr>';
                    totalContainer.textContent = '$0.00';
                    return;
                }

                let html = '';
                let grandTotal = 0;

                products.forEach(item => {
                    grandTotal += parseFloat(item.total_value);
                    html += `
                        <tr>
                            <td class="fw-medium">${escapeHtml(item.product_name)}</td>
                            <td class="text-end">${item.quantity}</td>
                            <td class="text-end">$${formatCurrency(item.price)}</td>
                            <td class="small text-muted">${formatDate(item.datetime)}</td>
                            <td class="text-end">$${formatCurrency(item.total_value)}</td>
                        </tr>
                    `;
                });

                listContainer.innerHTML = html;
                totalContainer.textContent = '$' + formatCurrency(grandTotal);
            }

            // Utilities
            function setLoading(isLoading) {
                submitBtn.disabled = isLoading;
                submitBtn.innerHTML = isLoading 
                    ? '<span class="spinner-border spinner-border-sm me-2"></span>Saving...' 
                    : 'Submit Entry';
            }

            function showAlert(type, message) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.getElementById('alert-container').innerHTML = alertHtml;
                // Auto-dismiss after 3s
                setTimeout(() => {
                    const alertEl = document.querySelector('.alert');
                    if (alertEl) bootstrap.Alert.getOrCreateInstance(alertEl).close();
                }, 3000);
            }

            function formatCurrency(val) {
                return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function formatDate(dateString) {
                return new Date(dateString).toLocaleString();
            }

            function escapeHtml(unsafe) {
                 return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            }
        });
    </script>
</body>
</html>