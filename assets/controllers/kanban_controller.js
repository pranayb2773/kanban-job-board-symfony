import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static targets = ['board', 'column', 'card'];
    static values = {
        boardId: Number
    };

    connect() {
        console.log('Kanban controller connected');
        this.initializeSortable();
        this.applicationToDelete = null;
    }

    initializeSortable() {
        // Initialize Sortable for each column
        this.columnTargets.forEach(column => {
            new Sortable(column, {
                group: 'kanban-cards',
                animation: 150,
                ghostClass: 'dragging',
                dragClass: 'dragging',
                onStart: (evt) => {
                    column.classList.add('drag-over');
                },
                onEnd: (evt) => {
                    // Remove drag-over class from all columns
                    this.columnTargets.forEach(col => col.classList.remove('drag-over'));

                    // Get the new status from the target column
                    const newStatus = evt.to.dataset.status;
                    const applicationId = evt.item.dataset.applicationId;

                    // Update the status via AJAX
                    this.updateApplicationStatus(applicationId, newStatus);
                }
            });
        });
    }

    async updateApplicationStatus(applicationId, newStatus) {
        try {
            const response = await fetch(`/job-board/application/${applicationId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status: newStatus })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                this.showToast(data.message || 'Failed to update status', 'error');
                // Reload page to reset card position
                window.location.reload();
            } else {
                this.showToast('Application status updated successfully', 'success');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            this.showToast('An error occurred. Please try again.', 'error');
            window.location.reload();
        }
    }

    async openAddModal(event) {
        const boardId = this.boardIdValue;
        const modal = new bootstrap.Modal(document.getElementById('addApplicationModal'));
        const modalContent = document.getElementById('addApplicationModalContent');

        try {
            const response = await fetch(`/job-board/${boardId}/_fragment/application-modal`);
            const html = await response.text();
            modalContent.innerHTML = html;

            // Bind form submit handler
            this.bindFormHandler('addApplicationModal', () => {
                modal.hide();
                window.location.reload();
            });

            modal.show();
        } catch (error) {
            console.error('Error loading add modal:', error);
            this.showToast('Failed to load form. Please try again.', 'error');
        }
    }

    async openDetailsModal(event) {
        const applicationId = event.currentTarget.dataset.applicationId;
        const modal = new bootstrap.Modal(document.getElementById('viewApplicationModal'));
        const modalContent = document.getElementById('viewApplicationModalContent');

        try {
            const response = await fetch(`/job-board/application/${applicationId}/details`);
            const application = await response.json();

            // Render the view modal with application data
            const html = this.renderViewModal(application);
            modalContent.innerHTML = html;

            modal.show();
        } catch (error) {
            console.error('Error loading details:', error);
            this.showToast('Failed to load application details. Please try again.', 'error');
        }
    }

    async openEditModal(event) {
        const applicationId = event.currentTarget.dataset.applicationId;
        const modal = new bootstrap.Modal(document.getElementById('editApplicationModal'));
        const modalContent = document.getElementById('editApplicationModalContent');

        // Close view modal if open
        const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewApplicationModal'));
        if (viewModal) {
            viewModal.hide();
        }

        try {
            const response = await fetch(`/job-board/application/${applicationId}/_fragment/edit-modal`);
            const html = await response.text();
            modalContent.innerHTML = html;

            // Bind form submit handler
            this.bindFormHandler('editApplicationModal', () => {
                modal.hide();
                window.location.reload();
            });

            modal.show();
        } catch (error) {
            console.error('Error loading edit modal:', error);
            this.showToast('Failed to load edit form. Please try again.', 'error');
        }
    }

    deleteApplication(event) {
        // Store the application ID and show confirmation modal
        this.applicationToDelete = event.currentTarget.dataset.applicationId;
        const confirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        confirmModal.show();
    }

    async confirmDelete() {
        if (!this.applicationToDelete) {
            return;
        }

        const applicationId = this.applicationToDelete;

        // Close confirmation modal
        const confirmModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal'));
        if (confirmModal) {
            confirmModal.hide();
        }

        try {
            const response = await fetch(`/job-board/application/${applicationId}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.showToast('Application deleted successfully', 'success');

                // Close edit modal and reload
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editApplicationModal'));
                if (editModal) {
                    editModal.hide();
                }

                setTimeout(() => window.location.reload(), 500);
            } else {
                this.showToast(data.message || 'Failed to delete application', 'error');
            }
        } catch (error) {
            console.error('Error deleting application:', error);
            this.showToast('An error occurred. Please try again.', 'error');
        } finally {
            this.applicationToDelete = null;
        }
    }

    bindFormHandler(modalId, onSuccess) {
        const modalElement = document.getElementById(modalId);
        const form = modalElement.querySelector('form');

        if (!form || form.dataset.ajaxBound) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        const defaultButtonContent = submitButton ? submitButton.innerHTML : '';
        form.dataset.ajaxBound = 'true';

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form)
                });

                const data = await response.json();

                if (response.ok && data?.success) {
                    this.showToast(data.message || 'Saved successfully!', 'success');
                    onSuccess();
                } else {
                    // Replace modal content with form errors
                    const modalContent = modalElement.querySelector('.modal-content');
                    modalContent.innerHTML = data?.modal;
                    this.bindFormHandler(modalId, onSuccess);
                    this.showToast(data?.message || 'Please fix the errors and try again.', 'error');
                }
            } catch (error) {
                console.error('Form submission error:', error);
                this.showToast('An error occurred. Please try again.', 'error');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = defaultButtonContent;
                }
            }
        });
    }

    renderViewModal(application) {
        return `
            <div class="modal-header align-items-center">
                <h5 class="modal-title d-flex align-items-center mb-0">
                    <span class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                        <i class="bi bi-briefcase"></i>
                    </span>
                    <span>Application Details</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <div class="row g-3">
                    <div class="col-12">
                        <h4 class="fw-bold mb-1">${this.escapeHtml(application.company)}</h4>
                        <p class="text-muted h6">${this.escapeHtml(application.jobTitle)}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted d-block mb-1">Location</label>
                        <div><i class="bi bi-geo-alt me-2"></i>${this.escapeHtml(application.location)}</div>
                    </div>
                    ${application.salary ? `
                        <div class="col-md-6">
                            <label class="small text-muted d-block mb-1">Salary Range</label>
                            <div class="text-success fw-semibold"><i class="bi bi-currency-pound"></i>${this.escapeHtml(application.salary)}</div>
                        </div>
                    ` : ''}
                    ${application.url ? `
                        <div class="col-12">
                            <label class="small text-muted d-block mb-1">Job Posting</label>
                            <div>
                                <a href="${this.escapeHtml(application.url)}" target="_blank" class="text-decoration-none">
                                    <i class="bi bi-link-45deg me-2"></i>${this.escapeHtml(application.url)}
                                </a>
                            </div>
                        </div>
                    ` : ''}
                    <div class="col-12">
                        <label class="small text-muted d-block mb-1">Status</label>
                        <span class="badge bg-primary">${this.capitalize(application.status)}</span>
                    </div>
                    <div class="col-12">
                        <label class="small text-muted d-block mb-1">Description / Notes</label>
                        <div class="border rounded p-3 bg-light">${this.nl2br(this.escapeHtml(application.description))}</div>
                    </div>
                    <div class="col-12">
                        <hr>
                        <div class="small text-muted">
                            <div class="mb-1"><strong>Created:</strong> ${application.createdAt}</div>
                            ${application.appliedAt ? `<div class="mb-1"><strong>Applied:</strong> ${application.appliedAt}</div>` : ''}
                            ${application.interviewedAt ? `<div class="mb-1"><strong>Interviewed:</strong> ${application.interviewedAt}</div>` : ''}
                            ${application.offeredAt ? `<div class="mb-1"><strong>Offered:</strong> ${application.offeredAt}</div>` : ''}
                            ${application.rejectedAt ? `<div class="mb-1"><strong>Rejected:</strong> ${application.rejectedAt}</div>` : ''}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-application-id="${application.id}" data-action="click->kanban#openEditModal">
                    <i class="bi bi-pencil me-1"></i>Edit
                </button>
            </div>
        `;
    }

    showToast(message, type = 'success') {
        const toastElement = document.getElementById('liveToast');
        if (!toastElement) return;

        const toastBody = toastElement.querySelector('.toast-body');
        toastBody.textContent = message;

        toastElement.classList.remove('text-bg-success', 'text-bg-danger');
        toastElement.classList.add(type === 'success' ? 'text-bg-success' : 'text-bg-danger');

        const toast = bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    nl2br(text) {
        return text.replace(/\n/g, '<br>');
    }

    capitalize(text) {
        return text.charAt(0).toUpperCase() + text.slice(1);
    }
}
