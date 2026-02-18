<?php
// This file is ONLY for Join Modal + JS
// It reuses existing POST logic in tournaments.php
?>

<!-- JOIN TOURNAMENT MODAL -->
<div id="joinModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <span class="modal-title">Join Tournament</span>
            <button class="close-modal" onclick="closeJoinModal()">&times;</button>
        </div>

        <form method="POST" id="joinForm">
            <input type="hidden" name="action" value="join_tournament">
            <input type="hidden" name="tournament_id" id="modalTournamentId">
            <input type="hidden" name="payment_method" id="selectedPaymentMethod">

            <div class="info-row">
                <span>Tournament:</span>
                <strong id="modalTournamentName">Loading...</strong>
            </div>

            <div class="info-row">
                <span>Entry Fee:</span>
                <strong id="modalFee">৳0</strong>
            </div>

            <div id="paymentSection">
                <p style="margin-top: 15px; font-size: 0.9rem; color: #94a3b8;">
                    Select Payment Method
                </p>

                <div class="payment-options">
                    <div class="pay-btn" onclick="selectPayment('bkash', this)">
                        📱 bKash
                    </div>
                    <div class="pay-btn" onclick="selectPayment('nagad', this)">
                        💳 Nagad
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeJoinModal()" style="flex:1;">
                    Cancel
                </button>
                <button type="submit" id="submitBtn" class="btn btn-primary" style="flex:1;">
                    Confirm Join
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal-overlay{
    display:none;
    position:fixed;
    top:0;left:0;
    width:100%;height:100%;
    background:rgba(0,0,0,.7);
    z-index:9999;
    justify-content:center;
    align-items:center;
}
.modal-content{
    background:#1e293b;
    padding:25px;
    border-radius:12px;
    width:90%;
    max-width:450px;
    border:1px solid #334155;
    color:#fff;
}
.modal-header{
    display:flex;
    justify-content:space-between;
    border-bottom:1px solid #334155;
    margin-bottom:15px;
    padding-bottom:10px;
}
.close-modal{
    background:none;
    border:none;
    font-size:22px;
    color:#94a3b8;
    cursor:pointer;
}
.payment-options{
    display:flex;
    gap:10px;
    margin:15px 0;
}
.pay-btn{
    flex:1;
    padding:10px;
    border:1px solid #334155;
    border-radius:8px;
    cursor:pointer;
    text-align:center;
}
.pay-btn.selected{
    background:#6366f1;
}
.info-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:10px;
}
.modal-footer{
    display:flex;
    gap:10px;
}
</style>

<script>
const modal = document.getElementById('joinModal');
const paymentInput = document.getElementById('selectedPaymentMethod');
const paymentSection = document.getElementById('paymentSection');
const submitBtn = document.getElementById('submitBtn');

function openJoinModal(id, name, fee){
    document.getElementById('modalTournamentId').value = id;
    document.getElementById('modalTournamentName').textContent = name;
    document.getElementById('modalFee').textContent = '৳' + fee;

    document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('selected'));
    paymentInput.value = '';

    if(fee > 0){
        paymentSection.style.display = 'block';
        submitBtn.textContent = 'Confirm & Pay';
    }else{
        paymentSection.style.display = 'none';
        paymentInput.value = 'Free';
        submitBtn.textContent = 'Confirm Join';
    }

    modal.style.display = 'flex';
}

function closeJoinModal(){
    modal.style.display = 'none';
}

function selectPayment(method, el){
    document.querySelectorAll('.pay-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    paymentInput.value = method;
}

window.onclick = function(e){
    if(e.target === modal){
        closeJoinModal();
    }
};
</script>
