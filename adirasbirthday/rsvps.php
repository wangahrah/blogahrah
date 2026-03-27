<?php
$rsvps = [];
$file = __DIR__ . '/../data/birthday-rsvps.json';
if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    if (is_array($data)) $rsvps = $data;
}

$attending = array_filter($rsvps, fn($r) => !empty($r['attending']));
$declined = array_filter($rsvps, fn($r) => empty($r['attending']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>RSVPs - Adira's Birthday</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  background: #0a0a0f;
  color: #D4AF37;
  font-family: 'Courier New', Courier, monospace;
  padding: 20px;
  max-width: 700px;
  margin: 0 auto;
}
h1 { font-size: 1.3rem; margin-bottom: 6px; }
.count {
  font-size: 1.1rem;
  margin-bottom: 20px;
  color: rgba(212, 175, 55, 0.7);
}
.count strong { color: #D4AF37; font-size: 1.3rem; }
h2 {
  font-size: 1rem;
  margin: 24px 0 10px 0;
  padding-bottom: 6px;
  border-bottom: 1px solid rgba(212, 175, 55, 0.2);
}
.card {
  background: rgba(212, 175, 55, 0.06);
  border: 1px solid rgba(212, 175, 55, 0.2);
  border-radius: 6px;
  padding: 14px;
  margin-bottom: 10px;
}
.card .name {
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 6px;
}
.card .detail {
  font-size: 0.85rem;
  color: rgba(212, 175, 55, 0.7);
  margin: 3px 0;
}
.card .detail span { color: #D4AF37; }
.house-gryffindor { border-left: 3px solid #740001; }
.house-slytherin { border-left: 3px solid #1A472A; }
.house-ravenclaw { border-left: 3px solid #0E1A40; }
.house-hufflepuff { border-left: 3px solid #FFD800; }
.declined .name { color: rgba(212, 175, 55, 0.4); }
.empty { color: rgba(212, 175, 55, 0.3); font-style: italic; margin: 10px 0; }
a { color: #D4AF37; }
</style>
</head>
<body>

<h1>&#x1F989; Adira's Birthday RSVPs</h1>
<p class="count"><strong><?= count($attending) ?></strong> attending &middot; <?= count($declined) ?> declined &middot; <?= count($rsvps) ?> total</p>

<h2>Attending (<?= count($attending) ?>)</h2>
<?php if (empty($attending)): ?>
  <p class="empty">No RSVPs yet.</p>
<?php endif; ?>
<?php foreach ($attending as $r): ?>
  <div class="card house-<?= htmlspecialchars($r['house'] ?? '') ?>">
    <div class="name"><?= htmlspecialchars($r['name']) ?></div>
    <?php if (!empty($r['house'])): ?>
      <div class="detail">House: <span><?= ucfirst(htmlspecialchars($r['house'])) ?></span></div>
    <?php endif; ?>
    <?php if (!empty($r['allergies'])): ?>
      <div class="detail">Allergies: <span><?= htmlspecialchars($r['allergies']) ?></span></div>
    <?php endif; ?>
    <div class="detail">RSVP'd: <span><?= htmlspecialchars($r['timestamp'] ?? '?') ?></span></div>
  </div>
<?php endforeach; ?>

<?php if (!empty($declined)): ?>
<h2>Declined (<?= count($declined) ?>)</h2>
<?php foreach ($declined as $r): ?>
  <div class="card declined">
    <div class="name"><?= htmlspecialchars($r['name']) ?></div>
  </div>
<?php endforeach; ?>
<?php endif; ?>

</body>
</html>
