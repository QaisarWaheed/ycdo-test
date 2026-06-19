<?php
include 'includes/connect.php';
// Get selected invoice from URL or POST if it exists
$selected_invoice = $_GET['invoice_no'] ?? '';

// Query 1: Get unique invoices that contain approved returns
$invoice_query = "SELECT DISTINCT r.invoice_no 
                  FROM return_purchase_items r 
                  WHERE r.return_purchase_item_status = 2";
$invoice_result = mysqli_query($con, $invoice_query);

// Query 2: Get items for the selected invoice (if one is chosen)
$items_result = null;
if ($selected_invoice) {
    $items_query = "SELECT r.*, i.name as item_name, p.batch_no, p.expiry_date
                    FROM return_purchase_items r
                    JOIN purchase_items p ON r.purchase_id = p.id
                    JOIN items i ON r.item_id = i.id
                    WHERE r.invoice_no = '" . mysqli_real_escape_string($con, $selected_invoice) . "' 
                    AND r.return_purchase_item_status = 2";
    $items_result = mysqli_query($con, $items_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Return to Party | Store Module</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dispatch Stock to Party</h1>
            <p class="text-sm text-slate-500">Select items from physical stock to return to the supplier/party.</p>
        </div>
        <a href="dashboard.php" class="text-sm font-medium text-slate-600 hover:text-blue-600 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>
    </div>

<div class="mb-8 bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
    <label class="block text-sm font-bold text-slate-700 mb-2">Select Approved Invoice</label>
    <select name="invoice_no" 
            onchange="window.location.href='?invoice_no=' + this.value" 
            class="w-full md:w-1/2 border-slate-300 rounded-lg p-2.5 border outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">-- Choose Invoice to view items --</option>
        <?php while($inv = mysqli_fetch_assoc($invoice_result)): ?>
            <option value="<?php echo $inv['invoice_no']; ?>" <?php echo ($selected_invoice == $inv['invoice_no']) ? 'selected' : ''; ?>>
                Invoice #: <?php echo $inv['invoice_no']; ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<?php if ($items_result && mysqli_num_rows($items_result) > 0): ?>
<form action="process_final_dispatch.php" method="POST">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                    <th class="p-4">Item Name</th>
                    <th class="p-4">Batch/Expiry</th>
                    <th class="p-4">Qty to Dispatch</th>
                    <th class="p-4 text-center">Physical Confirm</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                <tr>
                    <td class="p-4">
                        <div class="text-sm font-bold text-slate-800"><?php echo $item['item_name']; ?></div>
                        <input type="hidden" name="return_ids[]" value="<?php echo $item['return_purchase_item_id']; ?>">
                    </td>
                    <td class="p-4">
                        <span class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded">B: <?php echo $item['batch_no']; ?></span>
                        <span class="text-xs bg-gray-50 text-gray-600 px-2 py-1 rounded ml-1">E: <?php echo $item['expiry_date']; ?></span>
                    </td>
                    <td class="p-4">
                        <div class="text-sm font-black text-emerald-600"><?php echo $item['return_quantity']; ?> Units</div>
                    </td>
                    <td class="p-4 text-center">
                        <input type="checkbox" required class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
            <p class="text-xs text-slate-500 italic font-medium">Verify physical stock before clicking dispatch.</p>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-8 rounded-lg shadow-lg shadow-blue-200 transition-all uppercase text-xs tracking-widest">
                Confirm Physical Dispatch
            </button>
        </div>
    </div>
</form>
<?php elseif($selected_invoice): ?>
    <div class="p-10 text-center bg-white rounded-xl border border-dashed border-slate-300">
        <p class="text-slate-400">No approved items found for this invoice.</p>
    </div>
<?php endif; ?>
    <div class="mt-8 flex gap-4 p-4 bg-amber-50 border border-amber-100 rounded-lg">
        <div class="text-amber-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <h4 class="text-sm font-bold text-amber-800">Store Staff Notice:</h4>
            <p class="text-xs text-amber-700 mt-1">Please ensure the physical quantity matches the "Quantity to Dispatch" before clicking confirm. Once dispatched, stock will be deducted from the inventory immediately.</p>
        </div>
    </div>
</div>

</body>
</html>