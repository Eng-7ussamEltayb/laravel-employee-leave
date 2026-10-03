<?php
declare(strict_types=1);
$file = __DIR__ . '/../storage/leaves.json';
$leaves = json_decode((string)file_get_contents($file), true) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'create') {
        $leaves[] = [
            'id' => empty($leaves) ? 1 : max(array_column($leaves, 'id')) + 1,
            'employee' => trim((string)$_POST['employee']),
            'type' => $_POST['type'] ?? 'annual',
            'days' => (int)$_POST['days'],
            'status' => 'pending',
            'reason' => trim((string)$_POST['reason']),
        ];
    }
    if (in_array($action, ['approve', 'reject'], true)) {
        foreach ($leaves as &$row) {
            if ((int)$row['id'] === $id) {
                $row['status'] = $action === 'approve' ? 'approved' : 'rejected';
            }
        }
        unset($row);
    }
    file_put_contents($file, json_encode($leaves, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8"><title>نظام الإجازات</title>
<style>
body{font-family:Tahoma,Arial;background:#f1f5f9;padding:1.5rem}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{border:1px solid #e2e8f0;padding:.6rem;text-align:right}
.card{background:#fff;padding:1rem;border-radius:10px;margin-bottom:1rem}
.pending{color:#b45309}.approved{color:#15803d}.rejected{color:#b91c1c}
button{margin-left:.25rem}
</style>
</head>
<body>
<h1>نظام طلبات الإجازات (Demo)</h1>
<div class="card">
<form method="post">
<input type="hidden" name="action" value="create">
<label>الموظف <input name="employee" required></label>
<label>النوع
<select name="type"><option value="annual">سنوية</option><option value="sick">مرضية</option></select>
</label>
<label>الأيام <input type="number" name="days" min="1" value="1"></label>
<label>السبب <input name="reason" required></label>
<button type="submit">إرسال الطلب</button>
</form>
</div>
<table>
<tr><th>#</th><th>الموظف</th><th>النوع</th><th>الأيام</th><th>الحالة</th><th>إجراء</th></tr>
<?php foreach ($leaves as $row): ?>
<tr>
<td><?= (int)$row['id'] ?></td>
<td><?= htmlspecialchars($row['employee']) ?></td>
<td><?= htmlspecialchars($row['type']) ?></td>
<td><?= (int)$row['days'] ?></td>
<td class="<?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></td>
<td>
<?php if ($row['status'] === 'pending'): ?>
<form method="post" style="display:inline"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="action" value="approve"><button>موافقة</button></form>
<form method="post" style="display:inline"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><input type="hidden" name="action" value="reject"><button>رفض</button></form>
<?php else: ?>—<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
</body></html>
