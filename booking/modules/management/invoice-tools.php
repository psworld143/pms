<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Auth guard: manager or front_desk
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['manager','front_desk'])) {
	header('Location: ../../login.php');
	exit();
}

$page_title = 'Invoice Tools';
include __DIR__ . '/../../includes/header-unified.php';
include __DIR__ . '/../../includes/sidebar-unified.php';
?>

<main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1">
	<h2 class="text-2xl lg:text-3xl font-semibold text-gray-800 mb-6">Invoice Management Tools</h2>

	<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
		<!-- Actions -->
		<div class="bg-white rounded-lg shadow p-6 space-y-4">
			<h3 class="text-lg font-semibold text-gray-900">Run Actions</h3>
			<label class="block text-sm text-gray-700">Reservation ID or Number</label>
			<input id="resInput" type="text" class="w-full border rounded px-3 py-2" placeholder="e.g. 123 or RES2025..." />

			<div class="flex flex-wrap gap-3 mt-2">
				<button id="btnFinalize" class="px-3 py-2 bg-blue-600 text-white rounded">Finalize/Update Invoice</button>
				<button id="btnFetch" class="px-3 py-2 bg-gray-700 text-white rounded">Get Invoice Details</button>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
				<div>
					<label class="block text-sm text-gray-700">Deposit Amount</label>
					<input id="depositAmount" type="number" step="0.01" class="w-full border rounded px-3 py-2" placeholder="500.00" />
					<button id="btnDeposit" class="mt-2 px-3 py-2 bg-purple-600 text-white rounded w-full">Record Deposit</button>
				</div>
				<div>
					<label class="block text-sm text-gray-700">Payment Amount</label>
					<input id="paymentAmount" type="number" step="0.01" class="w-full border rounded px-3 py-2" placeholder="1234.56" />
					<label class="block text-sm text-gray-700 mt-2">Method</label>
					<select id="paymentMethod" class="w-full border rounded px-3 py-2">
						<option value="cash">Cash</option>
						<option value="credit_card">Credit Card</option>
						<option value="debit_card">Debit Card</option>
						<option value="bank_transfer">Bank Transfer</option>
						<option value="voucher">Voucher</option>
					</select>
					<button id="btnPayment" class="mt-2 px-3 py-2 bg-green-600 text-white rounded w-full">Record Payment</button>
				</div>
			</div>
		</div>

		<!-- Output -->
		<div class="bg-white rounded-lg shadow p-6">
			<h3 class="text-lg font-semibold text-gray-900 mb-2">Output</h3>
			<pre id="out" class="text-xs bg-gray-50 border rounded p-3 overflow-auto min-h-[320px]"></pre>
		</div>
	</div>
</main>

<script>
const out = document.getElementById('out');
function show(o){ try{ out.textContent = JSON.stringify(o, null, 2);}catch(e){ out.textContent = String(o);} }
function getRes(){ return document.getElementById('resInput').value.trim(); }

async function doGet(url){ const r = await fetch(url); try{ return await r.json(); }catch(e){ return {success:false, message:'Invalid JSON', raw: await r.text()}; } }
async function doPost(url, body){ const r = await fetch(url,{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)}); try{ return await r.json(); }catch(e){ return {success:false, message:'Invalid JSON', raw: await r.text()}; } }

// Buttons

document.getElementById('btnFinalize').onclick = async () => {
	const rid = getRes(); if(!rid){ return show({success:false,message:'Enter reservation'}); }
	const res = await doGet(`../../api/finalize-invoice.php?reservation_id=${encodeURIComponent(rid)}`);
	show(res);
};

document.getElementById('btnFetch').onclick = async () => {
	const rid = getRes(); if(!rid){ return show({success:false,message:'Enter reservation'}); }
	const res = await doGet(`../../api/get-invoice-details.php?reservation_id=${encodeURIComponent(rid)}`);
	show(res);
};

document.getElementById('btnDeposit').onclick = async () => {
	const rid = getRes(); const amt = parseFloat(document.getElementById('depositAmount').value||'0');
	if(!rid || !(amt>0)){ return show({success:false,message:'Enter reservation and positive deposit'}); }
	const res = await doPost(`../../api/record-deposit.php`, {reservation_id: rid, amount: amt, method: 'cash'});
	show(res);
};

document.getElementById('btnPayment').onclick = async () => {
	const rid = getRes(); const amt = parseFloat(document.getElementById('paymentAmount').value||'0'); const method = document.getElementById('paymentMethod').value;
	if(!rid || !(amt>0)){ return show({success:false,message:'Enter reservation and positive payment'}); }
	const res = await doPost(`../../api/post-payment.php`, {reservation_id: rid, amount: amt, method});
	show(res);
};
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
