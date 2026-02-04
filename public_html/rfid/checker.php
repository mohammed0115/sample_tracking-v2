<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_login();

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tag data
    $tagPower = trim($_POST['tag_power'] ?? '');
    $tagFrequency = trim($_POST['tag_frequency'] ?? '');
    $tagProtocol = trim($_POST['tag_protocol'] ?? '');
    $tagRange = (float)($_POST['tag_range'] ?? 0);
    
    // Reader data
    $readerFrequency = trim($_POST['reader_frequency'] ?? '');
    $readerProtocols = $_POST['reader_protocols'] ?? [];
    $readerRange = (float)($_POST['reader_range'] ?? 0);
    
    // Analysis
    $rfidType = '';
    $frequencyBand = '';
    $compatible = true;
    $reasons = [];
    $fixes = [];
    
    // Determine RFID Type
    if ($tagPower === 'Passive') {
        if ($tagFrequency === '125kHz') {
            $rfidType = 'Passive LF RFID';
            $frequencyBand = 'LF (125 kHz)';
        } elseif ($tagFrequency === '13.56MHz') {
            $rfidType = 'Passive HF RFID';
            $frequencyBand = 'HF (13.56 MHz)';
        } elseif ($tagFrequency === '860-960MHz') {
            $rfidType = 'Passive UHF RFID';
            $frequencyBand = 'UHF (860-960 MHz)';
        } elseif ($tagFrequency === '2.45GHz') {
            $rfidType = 'Passive Microwave RFID';
            $frequencyBand = 'Microwave (2.45 GHz)';
        }
    } elseif ($tagPower === 'Semi-Passive') {
        $rfidType = 'Semi-Passive (Battery-Assisted) RFID';
        $frequencyBand = match($tagFrequency) {
            '860-960MHz' => 'UHF (860-960 MHz)',
            '2.45GHz' => 'Microwave (2.45 GHz)',
            default => $tagFrequency
        };
    } elseif ($tagPower === 'Active') {
        $rfidType = 'Active RFID';
        $frequencyBand = match($tagFrequency) {
            '860-960MHz' => 'UHF (860-960 MHz)',
            '2.45GHz' => 'Microwave (2.45 GHz)',
            default => $tagFrequency
        };
    }
    
    // Check frequency compatibility
    if ($tagFrequency !== $readerFrequency) {
        $compatible = false;
        $reasons[] = __('checker.freq_mismatch') . " (Tag: $tagFrequency, Reader: $readerFrequency)";
        $fixes[] = __('checker.fix_freq');
    } else {
        $reasons[] = __('checker.freq_match');
    }
    
    // Check protocol compatibility
    if (!in_array($tagProtocol, $readerProtocols, true)) {
        $compatible = false;
        $reasons[] = __('checker.protocol_mismatch') . " (Tag: $tagProtocol)";
        $fixes[] = __('checker.fix_protocol');
    } else {
        $reasons[] = __('checker.protocol_match');
    }
    
    // Check range compatibility
    if ($tagRange > $readerRange) {
        $compatible = false;
        $reasons[] = __('checker.range_exceed') . " (Tag: {$tagRange}m, Reader: {$readerRange}m)";
        $fixes[] = __('checker.fix_range');
    } else {
        $reasons[] = __('checker.range_ok');
    }
    
    // Check active tag support
    if ($tagPower === 'Active') {
        $reasons[] = __('checker.active_tag');
        $fixes[] = __('checker.fix_active');
    }
    
    $result = [
        'rfid_type' => $rfidType,
        'frequency_band' => $frequencyBand,
        'compatible' => $compatible,
        'reasons' => $reasons,
        'fixes' => $fixes,
        'tag_power' => $tagPower,
        'tag_frequency' => $tagFrequency,
        'tag_protocol' => $tagProtocol,
        'tag_range' => $tagRange,
        'reader_frequency' => $readerFrequency,
        'reader_protocols' => implode(', ', $readerProtocols),
        'reader_range' => $readerRange
    ];
}

ob_start();
?>
<h1><?= e(__('checker.title')) ?></h1>

<div class="card">
    <h3><?= e(__('checker.description')) ?></h3>
    <form method="post">
        <div class="grid-2" style="margin-bottom:20px;">
            <div class="card" style="background:#f8f9fa;">
                <h4><?= e(__('checker.tag_specs')) ?></h4>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('checker.power_type')) ?></label>
                    <select name="tag_power" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="Passive" <?= ($result['tag_power'] ?? '') === 'Passive' ? 'selected' : '' ?>>Passive</option>
                        <option value="Semi-Passive" <?= ($result['tag_power'] ?? '') === 'Semi-Passive' ? 'selected' : '' ?>>Semi-Passive</option>
                        <option value="Active" <?= ($result['tag_power'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('checker.frequency')) ?></label>
                    <select name="tag_frequency" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="125kHz" <?= ($result['tag_frequency'] ?? '') === '125kHz' ? 'selected' : '' ?>>125 kHz (LF)</option>
                        <option value="13.56MHz" <?= ($result['tag_frequency'] ?? '') === '13.56MHz' ? 'selected' : '' ?>>13.56 MHz (HF)</option>
                        <option value="860-960MHz" <?= ($result['tag_frequency'] ?? '') === '860-960MHz' ? 'selected' : '' ?>>860-960 MHz (UHF)</option>
                        <option value="2.45GHz" <?= ($result['tag_frequency'] ?? '') === '2.45GHz' ? 'selected' : '' ?>>2.45 GHz (Microwave)</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('checker.protocol')) ?></label>
                    <select name="tag_protocol" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="ISO 14443A" <?= ($result['tag_protocol'] ?? '') === 'ISO 14443A' ? 'selected' : '' ?>>ISO 14443A</option>
                        <option value="ISO 14443B" <?= ($result['tag_protocol'] ?? '') === 'ISO 14443B' ? 'selected' : '' ?>>ISO 14443B</option>
                        <option value="ISO 15693" <?= ($result['tag_protocol'] ?? '') === 'ISO 15693' ? 'selected' : '' ?>>ISO 15693</option>
                        <option value="EPC Gen2" <?= ($result['tag_protocol'] ?? '') === 'EPC Gen2' ? 'selected' : '' ?>>EPC Gen2</option>
                        <option value="ISO 18000-6C" <?= ($result['tag_protocol'] ?? '') === 'ISO 18000-6C' ? 'selected' : '' ?>>ISO 18000-6C</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('checker.read_range')) ?> (<?= e(__('checker.meters')) ?>)</label>
                    <input type="number" step="0.1" name="tag_range" required value="<?= e($result['tag_range'] ?? '') ?>">
                </div>
            </div>
            
            <div class="card" style="background:#f8f9fa;">
                <h4><?= e(__('checker.reader_specs')) ?></h4>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('checker.frequency')) ?></label>
                    <select name="reader_frequency" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="125kHz" <?= ($result['reader_frequency'] ?? '') === '125kHz' ? 'selected' : '' ?>>125 kHz (LF)</option>
                        <option value="13.56MHz" <?= ($result['reader_frequency'] ?? '') === '13.56MHz' ? 'selected' : '' ?>>13.56 MHz (HF)</option>
                        <option value="860-960MHz" <?= ($result['reader_frequency'] ?? '') === '860-960MHz' ? 'selected' : '' ?>>860-960 MHz (UHF)</option>
                        <option value="2.45GHz" <?= ($result['reader_frequency'] ?? '') === '2.45GHz' ? 'selected' : '' ?>>2.45 GHz (Microwave)</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('checker.protocols')) ?></label>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-top:6px;">
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_protocols[]" value="ISO 14443A"> ISO 14443A</label>
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_protocols[]" value="ISO 14443B"> ISO 14443B</label>
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_protocols[]" value="ISO 15693"> ISO 15693</label>
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_protocols[]" value="EPC Gen2"> EPC Gen2</label>
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_protocols[]" value="ISO 18000-6C"> ISO 18000-6C</label>
                    </div>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('checker.max_range')) ?> (<?= e(__('checker.meters')) ?>)</label>
                    <input type="number" step="0.1" name="reader_range" required value="<?= e($result['reader_range'] ?? '') ?>">
                </div>
            </div>
        </div>
        
        <button class="btn btn-blue" type="submit"><?= e(__('checker.analyze')) ?></button>
    </form>
</div>

<?php if ($result): ?>
<div class="card" style="margin-top:20px;">
    <h3><?= e(__('checker.result')) ?></h3>
    
    <div class="info-item">
        <span><?= e(__('checker.rfid_type')) ?>:</span>
        <strong><?= e($result['rfid_type']) ?></strong>
    </div>
    
    <div class="info-item">
        <span><?= e(__('checker.freq_band')) ?>:</span>
        <strong><?= e($result['frequency_band']) ?></strong>
    </div>
    
    <div class="info-item">
        <span><?= e(__('checker.status')) ?>:</span>
        <span class="status <?= $result['compatible'] ? 'approved' : 'rejected' ?>">
            <?= $result['compatible'] ? e(__('checker.compatible')) : e(__('checker.not_compatible')) ?>
        </span>
    </div>
    
    <div style="margin-top:20px;">
        <h4><?= e(__('checker.analysis')) ?>:</h4>
        <ul style="padding-right:20px;">
            <?php foreach ($result['reasons'] as $reason): ?>
                <li><?= e($reason) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <?php if (!$result['compatible']): ?>
    <div style="margin-top:20px;padding:12px;background:#fff3cd;border-radius:8px;">
        <h4 style="color:#856404;"><?= e(__('checker.recommendations')) ?>:</h4>
        <ul style="padding-right:20px;color:#856404;">
            <?php foreach ($result['fixes'] as $fix): ?>
                <li><?= e($fix) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = __('checker.title');
$active = 'checker';
include __DIR__ . '/../partials/layout.php';
