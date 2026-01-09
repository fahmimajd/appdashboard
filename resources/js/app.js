import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Make Chart.js available globally
window.Chart = Chart;

// Global utilities
window.utils = {
    // Format number with thousand separator
    formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    },

    // Format date
    formatDate(date) {
        return new Intl.DateTimeFormat('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    },

    // Format percentage
    formatPercent(num, decimals = 2) {
        return parseFloat(num).toFixed(decimals) + '%';
    },

    // Show toast notification
    toast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                    type === 'warning' ? 'bg-yellow-500' :
                        'bg-blue-500'
            } z-50 transition-opacity duration-300`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    // Confirm dialog
    confirm(message) {
        return window.confirm(message);
    }
};

// Alpine.js data components
document.addEventListener('alpine:init', () => {
    // Sidebar toggle
    Alpine.data('sidebar', () => ({
        open: true,
        toggle() {
            this.open = !this.open;
        }
    }));

    // Data table with search and pagination
    Alpine.data('dataTable', (initialData = []) => ({
        data: initialData,
        filteredData: [],
        search: '',
        currentPage: 1,
        perPage: 10,
        sortColumn: null,
        sortDirection: 'asc',

        init() {
            this.filteredData = this.data;
        },

        get paginatedData() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredData.slice(start, end);
        },

        get totalPages() {
            return Math.ceil(this.filteredData.length / this.perPage);
        },

        filterData() {
            if (!this.search) {
                this.filteredData = this.data;
                return;
            }

            const searchLower = this.search.toLowerCase();
            this.filteredData = this.data.filter(item => {
                return Object.values(item).some(value =>
                    String(value).toLowerCase().includes(searchLower)
                );
            });
            this.currentPage = 1;
        },

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }

            this.filteredData.sort((a, b) => {
                let aVal = a[column];
                let bVal = b[column];

                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }

                if (this.sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        goToPage(page) {
            this.currentPage = page;
        }
    }));

    // Form validation
    Alpine.data('formValidation', () => ({
        errors: {},

        validate(field, rules) {
            const value = this[field];
            this.errors[field] = null;

            if (rules.required && !value) {
                this.errors[field] = 'Field ini wajib diisi';
                return false;
            }

            if (rules.minLength && value.length < rules.minLength) {
                this.errors[field] = `Minimal ${rules.minLength} karakter`;
                return false;
            }

            if (rules.maxLength && value.length > rules.maxLength) {
                this.errors[field] = `Maksimal ${rules.maxLength} karakter`;
                return false;
            }

            if (rules.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                this.errors[field] = 'Format email tidak valid';
                return false;
            }

            if (rules.numeric && !/^\d+$/.test(value)) {
                this.errors[field] = 'Harus berupa angka';
                return false;
            }

            return true;
        },

        hasError(field) {
            return this.errors[field] !== null && this.errors[field] !== undefined;
        }
    }));
});

// Chart helpers
window.chartHelpers = {
    // Default chart options
    defaultOptions: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            }
        }
    },

    // Color palette
    colors: {
        primary: '#3b82f6',
        secondary: '#6b7280',
        success: '#10b981',
        danger: '#ef4444',
        warning: '#f59e0b',
        info: '#06b6d4',
    },

    // Create line chart
    createLineChart(ctx, data, options = {}) {
        return new Chart(ctx, {
            type: 'line',
            data: data,
            options: { ...this.defaultOptions, ...options }
        });
    },

    // Create bar chart
    createBarChart(ctx, data, options = {}) {
        return new Chart(ctx, {
            type: 'bar',
            data: data,
            options: { ...this.defaultOptions, ...options }
        });
    },

    // Create pie chart
    createPieChart(ctx, data, options = {}) {
        return new Chart(ctx, {
            type: 'pie',
            data: data,
            options: { ...this.defaultOptions, ...options }
        });
    }
};
