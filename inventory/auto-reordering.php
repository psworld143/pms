<?php
/**
 * Auto Reordering - Manager Module
 */

require_once __DIR__ . '/../vps_session_fix.php';
require_once __DIR__ . '/../includes/database.php';

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'manager')) {
    header('Location: login.php?error=access_denied');
    exit();
}

$page_title = 'Auto Reordering';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Hotel Inventory System</title>
    <link rel="icon" type="image/png" href="../../assets/images/seait-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#10B981', secondary: '#059669' } } }
        }
    </script>
</head>
<body class="bg-gray-50">
<div class="flex min-h-screen">
    <?php include 'includes/inventory-header.php'; ?>
    <?php include 'includes/sidebar-inventory.php'; ?>

    <main class="lg:ml-64 mt-16 p-4 lg:p-6 flex-1">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl lg:text-3xl font-semibold text-gray-800">Auto Reordering</h2>
            <div class="flex items-center gap-3">
                <button id="seed-rules-btn" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded">
                    <i class="fas fa-magic mr-2"></i>Create Starter Rules
                </button>
                <button id="generate-po-btn" class="bg-primary hover:bg-secondary text-white px-4 py-2 rounded">
                    <i class="fas fa-file-invoice mr-2"></i>Generate Suggested PO
                </button>
                <button id="export-po-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    <i class="fas fa-download mr-2"></i>Export PDF
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-boxes text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Items With Rules</p>
                        <p id="stat-items-with-rules" class="text-2xl font-semibold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Below Threshold</p>
                        <p id="stat-below-threshold" class="text-2xl font-semibold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-truck text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Suppliers</p>
                        <p id="stat-suppliers" class="text-2xl font-semibold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Reorder Rules</h3>
                <div class="flex gap-2">
                    <input id="search" type="text" class="border rounded px-3 py-1" placeholder="Search item..."/>
                    <button id="refresh-btn" class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200"><i class="fas fa-sync"></i></button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="rules-tbody" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
            </div>
        </div>

        <!-- Modal for Suggested PO -->
        <div id="po-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow max-w-3xl w-full">
                    <div class="flex items-center justify-between p-4 border-b">
                        <h4 class="font-semibold">Suggested Purchase Order</h4>
                        <button id="close-po-modal" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="p-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Current</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Min</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Order Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                </tr>
                            </thead>
                            <tbody id="po-tbody" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t flex justify-end gap-2">
                        <button id="po-export-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"><i class="fas fa-download mr-2"></i>Export PDF</button>
                        <button id="po-close-btn" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
$(function(){
    loadRules();

    $('#refresh-btn').on('click', loadRules);
    $('#search').on('input', function(){ filterTable($(this).val()); });

    $('#generate-po-btn').on('click', function(){
        $.ajax({ url:'api/generate-po.php', dataType:'json', xhrFields:{withCredentials:true}, success:function(resp){
            if(!resp.success){ alert(resp.message||'Failed'); return; }
            const tbody = $('#po-tbody').empty();
            if (!resp.items || resp.items.length === 0){
                alert('No items are below their Min Level yet, or there are no reorder rules. Click "Create Starter Rules" first, then set Min Level < Current to test.');
                return;
            }
            resp.items.forEach(function(r){
                tbody.append(`<tr>
                    <td class="px-4 py-2 text-sm text-gray-900">${r.name}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${r.current}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${r.min}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${r.order_qty}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${r.supplier||'N/A'}</td>
                </tr>`);
            });
            $('#po-modal').removeClass('hidden');
        }, error:function(){ alert('Failed to generate PO (session or network).'); }});
    });
    $('#close-po-modal,#po-close-btn').on('click', function(){ $('#po-modal').addClass('hidden'); });
    $('#po-export-btn,#export-po-btn').on('click', function(){ window.open('api/generate-po.php?format=pdf','_blank'); });
    $('#seed-rules-btn').on('click', function(){
        if(!confirm('Create starter rules (Min=10, Reorder=20) for all items without rules?')) return;
        $.ajax({ url:'api/seed-reorder-rules.php', method:'POST', dataType:'json', xhrFields:{withCredentials:true}, success:function(r){
            if(!r.success){ alert(r.message||'Seeding failed'); } else { loadRules(); }
        }, error:function(){ alert('Seeding failed (session or network).'); }});
    });

    function filterTable(q){
        q = (q||'').toLowerCase();
        $('#rules-tbody tr').each(function(){
            const show = $(this).text().toLowerCase().indexOf(q) !== -1;
            $(this).toggle(show);
        });
    }

    // helper to append one editable rule row
    function appendRuleRow(it, suppliers){
        const row = $(`
            <tr>
                <td class="px-6 py-3 text-sm">
                    <div class="font-medium text-gray-900">${it.name}</div>
                    <div class="text-gray-500 text-xs">${it.sku||''}</div>
                </td>
                <td class="px-6 py-3 text-sm text-gray-900">${it.current||0}</td>
                <td class="px-6 py-3 text-sm"><input type="number" min="0" value="${it.min_level||0}" class="border rounded px-2 py-1 w-24 min-input"></td>
                <td class="px-6 py-3 text-sm"><input type="number" min="0" value="${it.reorder_qty||0}" class="border rounded px-2 py-1 w-24 qty-input"></td>
                <td class="px-6 py-3 text-sm">
                    <select class="border rounded px-2 py-1 supplier-input w-48">
                        <option value="">No supplier</option>
                        ${(suppliers||[]).map(s=>`<option value="${s.id}" ${it.supplier_id==s.id?'selected':''}>${s.name}</option>`).join('')}
                    </select>
                </td>
                <td class="px-6 py-3 text-sm">
                    <button class="save-btn bg-primary hover:bg-secondary text-white px-3 py-1 rounded mr-2"><i class="fas fa-save mr-1"></i>Save</button>
                    <button class="del-btn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded"><i class="fas fa-trash mr-1"></i>Delete</button>
                </td>
            </tr>`);
        row.find('.save-btn').on('click', function(){
            const min = row.find('.min-input').val();
            const qty = row.find('.qty-input').val();
            const sup = row.find('.supplier-input').val();
            $.ajax({
                url:'api/save-reorder-rule.php',
                method:'POST',
                dataType:'json',
                xhrFields:{withCredentials:true},
                data:{ item_id: it.id, min_level: min, reorder_qty: qty, supplier_id: sup },
                success:function(r){ if(!r.success){ alert(r.message||'Save failed'); } else { loadRules(); } },
                error:function(){ alert('Save failed (session or network).'); }
            });
        });
        row.find('.del-btn').on('click', function(){
            if(!confirm('Delete rule for this item?')) return;
            $.ajax({
                url:'api/delete-reorder-rule.php',
                method:'POST',
                dataType:'json',
                xhrFields:{withCredentials:true},
                data:{ item_id: it.id },
                success:function(r){ if(!r.success){ alert(r.message||'Delete failed'); } else { loadRules(); } },
                error:function(){ alert('Delete failed (session or network).'); }
            });
        });
        $('#rules-tbody').append(row);
    }

    function loadRules(){
        $.ajax({ url:'api/get-reorder-rules.php', dataType:'json', xhrFields:{withCredentials:true}, success:function(resp){
            if(!resp.success){ alert(resp.message||'Failed'); return; }
            $('#stat-items-with-rules').text(resp.stats.items_with_rules);
            $('#stat-below-threshold').text(resp.stats.below_threshold);
            $('#stat-suppliers').text(resp.stats.suppliers);
            const tbody = $('#rules-tbody').empty();
            if (resp.items && resp.items.length) {
                resp.items.forEach(function(it){ appendRuleRow(it, resp.suppliers||[]); });
            } else {
                // No rules yet: show base items so user can create rules
                $.ajax({ url:'api/get-inventory-items.php', dataType:'json', xhrFields:{withCredentials:true}, success:function(r){
                    if (!r.success) return;
                    const base = (r.inventory_items||[]).slice(0,200);
                    if (!base.length) return;
                    base.forEach(function(x){
                        appendRuleRow({ id:x.id, name:x.item_name||x.name, sku:x.sku, current:x.quantity||x.current_stock||0, min_level:0, reorder_qty:0, supplier_id:null }, resp.suppliers||[]);
                    });
                }});
            }
        }, error:function(){ console.error('Failed to load rules'); }});
    }
});
</script>
</body>
</html>


