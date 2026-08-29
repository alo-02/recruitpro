/**
 * RecruitPro - Main JavaScript
 */
document.addEventListener('DOMContentLoaded', function () {
    initSearchForm();
    initJobFilters();
    initKanbanDrag();
    initToasts();
    initIrisGaze();
});

function initIrisGaze() {
    const stage = document.getElementById('home-iris-stage');
    const pupil = stage?.querySelector('.rp-iris-pupil');
    const glint = stage?.querySelector('.rp-iris-glint');
    if (!stage || !pupil) return;

    stage.addEventListener('mousemove', function (e) {
        const box = stage.getBoundingClientRect();
        const x = (e.clientX - box.left) / box.width - 0.5;
        const y = (e.clientY - box.top) / box.height - 0.5;
        const dx = Math.max(-3.2, Math.min(3.2, x * 7));
        const dy = Math.max(-2.4, Math.min(2.4, y * 6));
        pupil.setAttribute('transform', 'translate(' + dx + ' ' + dy + ')');
        if (glint) glint.setAttribute('transform', 'translate(' + dx + ' ' + dy + ')');
    });

    stage.addEventListener('mouseleave', function () {
        pupil.removeAttribute('transform');
        if (glint) glint.removeAttribute('transform');
    });
}

function initSearchForm() {
    const searchForm = document.getElementById('hero-search-form');
    if (!searchForm) return;

    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const keyword = document.getElementById('search-keyword')?.value || '';
        const location = document.getElementById('search-location')?.value || '';
        const category = document.getElementById('search-category')?.value || '';
        const jobType = document.getElementById('search-type')?.value || '';
        const mode = document.getElementById('search-mode')?.value || '';
        const params = new URLSearchParams();
        if (keyword) params.set('q', keyword);
        if (location) params.set('location', location);
        if (category) params.append('category[]', category);
        if (jobType) params.append('job_type[]', jobType);
        if (mode) params.append('location_type[]', mode);
        window.location.href = '/jobs?' + params.toString();
    });
}

function initJobFilters() {
    const filterForm = document.getElementById('job-filters');
    if (!filterForm) return;

    filterForm.addEventListener('change', function () {
        filterJobs();
    });

    const clearBtn = document.getElementById('clear-filters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            filterForm.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            filterForm.querySelectorAll('input[type="range"]').forEach(r => r.value = r.max);
            filterJobs();
        });
    }

    const salaryRange = document.getElementById('salary-range');
    const salaryLabel = document.getElementById('salary-label');
    if (salaryRange && salaryLabel) {
        salaryRange.addEventListener('input', function () {
            salaryLabel.textContent = '$' + this.value + 'k+';
        });
    }
}

function filterJobs() {
    const jobCards = document.querySelectorAll('[data-job-type]');
    const checkedTypes = Array.from(document.querySelectorAll('input[name="job_type"]:checked')).map(el => el.value);
    const checkedLocations = Array.from(document.querySelectorAll('input[name="location_type"]:checked')).map(el => el.value);

    jobCards.forEach(card => {
        const type = card.dataset.jobType;
        const location = card.dataset.locationType;
        const typeMatch = checkedTypes.length === 0 || checkedTypes.includes(type);
        const locationMatch = checkedLocations.length === 0 || checkedLocations.includes(location);
        card.closest('.col-12, .job-list-item')?.classList.toggle('d-none', !(typeMatch && locationMatch));
    });

    const count = document.querySelectorAll('.job-list-item:not(.d-none)').length;
    const countEl = document.getElementById('job-count');
    if (countEl) countEl.textContent = count + ' jobs found';
}

function initKanbanDrag() {
    const cards = document.querySelectorAll('.kanban-card[draggable="true"]');
    const columns = document.querySelectorAll('.kanban-drop-zone');

    cards.forEach(card => {
        card.addEventListener('dragstart', function (e) {
            e.dataTransfer.setData('text/plain', this.id);
            this.classList.add('opacity-50');
        });
        card.addEventListener('dragend', function () {
            this.classList.remove('opacity-50');
        });
    });

    columns.forEach(col => {
        col.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('border-primary');
        });
        col.addEventListener('dragleave', function () {
            this.classList.remove('border-primary');
        });
        col.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('border-primary');
            const id = e.dataTransfer.getData('text/plain');
            const card = document.getElementById(id);
            if (card) this.appendChild(card);
        });
    });
}

function initToasts() {
    const toastElList = document.querySelectorAll('.toast');
    toastElList.forEach(el => {
        new bootstrap.Toast(el, { delay: 4000 }).show();
    });
}

function showToast(message, type = 'success') {
    const container = document.querySelector('.rp-toast-container') || createToastContainer();
    const icon = type === 'success' ? 'check_circle' : 'info';
    const bgClass = type === 'success' ? 'text-bg-success' : 'text-bg-primary';

    const toast = document.createElement('div');
    toast.className = 'toast align-items-center ' + bgClass + ' border-0';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <span class="material-symbols-outlined">${icon}</span>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
    container.appendChild(toast);
    new bootstrap.Toast(toast, { delay: 4000 }).show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'rp-toast-container';
    document.body.appendChild(container);
    return container;
}

function selectJobCard(el) {
    document.querySelectorAll('.job-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}
