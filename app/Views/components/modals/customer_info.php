<?php
// =====================================
// app/Views/components/modals/customer_info.php
// =====================================
?>
<!-- Customer Information Modal -->
<div class="modal fade" id="customerInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Customer Information
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customerForm">
                    <div class="form-group mb-3">
                        <label for="customer-full-name">Full Name *</label>
                        <input type="text" class="form-control" id="customer-full-name" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="customer-phone">Phone Number</label>
                        <input type="tel" class="form-control" id="customer-phone" placeholder="+63">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="customer-email-modal">Email Address</label>
                        <input type="email" class="form-control" id="customer-email-modal">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="customer-birthday">Birthday</label>
                        <input type="date" class="form-control" id="customer-birthday">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="customer-address">Address</label>
                        <textarea class="form-control" id="customer-address" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="join-loyalty">
                        <label class="form-check-label" for="join-loyalty">
                            Join our loyalty program
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="newsletter-subscribe">
                        <label class="form-check-label" for="newsletter-subscribe">
                            Subscribe to newsletter for promotions
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" onclick="saveCustomerInfo()">
                    <i class="fas fa-save"></i> Save Customer
                </button>
            </div>
        </div>
    </div>
</div>