<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/lang.php';

require_login();

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tag Information
    $tagType = trim($_POST['tag_type'] ?? '');
    $frequencyBand = trim($_POST['frequency_band'] ?? '');
    $frequencyValue = trim($_POST['frequency_value'] ?? '');
    $protocol = trim($_POST['protocol'] ?? '');
    $memoryType = trim($_POST['memory_type'] ?? '');
    $uidImmutable = trim($_POST['uid_immutable'] ?? '');
    $tamperResistance = trim($_POST['tamper_resistance'] ?? '');
    
    // Reader Information
    $readerFrequencies = $_POST['reader_frequencies'] ?? [];
    $readerStandards = $_POST['reader_standards'] ?? [];
    $authSupport = trim($_POST['auth_support'] ?? '');
    $secureLogging = trim($_POST['secure_logging'] ?? '');
    
    // Forensic Context
    $evidenceType = trim($_POST['evidence_type'] ?? '');
    $readRange = trim($_POST['read_range'] ?? '');
    $environment = trim($_POST['environment'] ?? '');
    $sensitivityLevel = trim($_POST['sensitivity_level'] ?? '');
    
    // Validation
    $approved = true;
    $reasons = [];
    $risks = [];
    $recommendations = [];
    
    // Rule 1: UID must be immutable
    if ($uidImmutable !== 'Yes') {
        $approved = false;
        $reasons[] = __('forensic.uid_mutable');
        $recommendations[] = __('forensic.rec_uid_immutable');
    }
    
    // Rule 2: No UHF for high sensitivity
    if ($frequencyBand === 'UHF' && $sensitivityLevel === 'High') {
        $approved = false;
        $reasons[] = __('forensic.uhf_high_sensitivity');
        $recommendations[] = __('forensic.rec_use_hf');
    }
    
    // Rule 3: Encryption/Authentication for Medium/High sensitivity
    if (in_array($sensitivityLevel, ['Medium', 'High'], true) && $authSupport === 'None') {
        $approved = false;
        $reasons[] = __('forensic.no_auth_required');
        $recommendations[] = __('forensic.rec_enable_auth');
    }
    
    // Rule 4: No Active RFID in labs
    if ($tagType === 'Active' && $environment === 'Lab') {
        $approved = false;
        $reasons[] = __('forensic.active_in_lab');
        $recommendations[] = __('forensic.rec_passive_tag');
    }
    
    // Rule 5: Timestamped logging required
    if ($secureLogging !== 'Yes') {
        $approved = false;
        $reasons[] = __('forensic.no_logging');
        $recommendations[] = __('forensic.rec_enable_logging');
    }
    
    // Frequency compatibility check
    if (!in_array($frequencyBand, $readerFrequencies, true)) {
        $approved = false;
        $reasons[] = __('forensic.freq_mismatch');
    }
    
    // Protocol compatibility check
    if (!in_array($protocol, $readerStandards, true)) {
        $approved = false;
        $reasons[] = __('forensic.protocol_mismatch');
    }
    
    // Security Assessment
    $securityScore = 0;
    $securityDetails = [];
    
    if ($uidImmutable === 'Yes') {
        $securityScore += 20;
        $securityDetails[] = __('forensic.sec_uid_ok');
    }
    
    if ($memoryType === 'Encrypted') {
        $securityScore += 30;
        $securityDetails[] = __('forensic.sec_encrypted');
    } elseif ($memoryType === 'Read-Only') {
        $securityScore += 15;
        $securityDetails[] = __('forensic.sec_readonly');
    }
    
    if ($tamperResistance === 'Cryptographic') {
        $securityScore += 30;
        $securityDetails[] = __('forensic.sec_crypto');
    } elseif ($tamperResistance === 'Physical Seal') {
        $securityScore += 15;
        $securityDetails[] = __('forensic.sec_physical');
    }
    
    if ($authSupport === 'Mutual Auth') {
        $securityScore += 20;
        $securityDetails[] = __('forensic.sec_mutual_auth');
    } elseif ($authSupport === 'AES') {
        $securityScore += 10;
        $securityDetails[] = __('forensic.sec_aes');
    }
    
    // Chain of Custody Compliance
    $chainCompliant = ($uidImmutable === 'Yes' && $secureLogging === 'Yes');
    
    // Legal Risk Assessment
    $legalRisk = 'Low';
    if ($sensitivityLevel === 'High' && !$chainCompliant) {
        $legalRisk = 'High';
    } elseif ($sensitivityLevel === 'High' || !$chainCompliant) {
        $legalRisk = 'Medium';
    } elseif ($sensitivityLevel === 'Medium' && $securityScore < 50) {
        $legalRisk = 'Medium';
    }
    
    // Recommended Configuration
    $recommendedConfig = [
        __('forensic.config_tag') => 'Passive HF',
        __('forensic.config_freq') => '13.56 MHz',
        __('forensic.config_protocol') => 'ISO 15693',
        __('forensic.config_memory') => 'Encrypted',
        __('forensic.config_uid') => __('forensic.immutable'),
        __('forensic.config_tamper') => 'Cryptographic',
        __('forensic.config_auth') => 'Mutual Auth',
        __('forensic.config_logging') => __('forensic.enabled')
    ];
    
    $result = [
        'approved' => $approved,
        'rfid_type' => $tagType . ' ' . $frequencyBand . ' RFID',
        'frequency_validation' => in_array($frequencyBand, $readerFrequencies, true),
        'security_score' => $securityScore,
        'security_details' => $securityDetails,
        'chain_compliant' => $chainCompliant,
        'legal_risk' => $legalRisk,
        'reasons' => $reasons,
        'recommendations' => $recommendations,
        'recommended_config' => $recommendedConfig
    ];
}

ob_start();
?>
<h1><?= e(__('forensic.title')) ?></h1>

<div class="card">
    <h3><?= e(__('forensic.description')) ?></h3>
    <form method="post">
        <div class="grid-2" style="margin-bottom:20px;">
            <!-- RFID Tag Information -->
            <div class="card" style="background:#f8f9fa;">
                <h4><?= e(__('forensic.tag_info')) ?></h4>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.tag_type')) ?> *</label>
                    <select name="tag_type" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="Passive">Passive</option>
                        <option value="Semi-Passive">Semi-Passive</option>
                        <option value="Active">Active</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.freq_band')) ?> *</label>
                    <select name="frequency_band" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="LF">LF (Low Frequency)</option>
                        <option value="HF">HF (High Frequency)</option>
                        <option value="UHF">UHF (Ultra High Frequency)</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.freq_value')) ?> *</label>
                    <select name="frequency_value" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="125kHz">125 kHz</option>
                        <option value="13.56MHz">13.56 MHz</option>
                        <option value="860-960MHz">860-960 MHz</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.protocol')) ?> *</label>
                    <select name="protocol" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="ISO 14443">ISO 14443</option>
                        <option value="ISO 15693">ISO 15693</option>
                        <option value="EPC Gen2">EPC Gen2</option>
                        <option value="ISO 18000-6C">ISO 18000-6C</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.memory_type')) ?> *</label>
                    <select name="memory_type" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="Read-Only">Read-Only</option>
                        <option value="Read/Write">Read/Write</option>
                        <option value="Encrypted">Encrypted</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.uid_immutable')) ?> *</label>
                    <select name="uid_immutable" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="Yes"><?= e(__('forensic.yes')) ?></option>
                        <option value="No"><?= e(__('forensic.no')) ?></option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.tamper')) ?> *</label>
                    <select name="tamper_resistance" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="None">None</option>
                        <option value="Physical Seal">Physical Seal</option>
                        <option value="Cryptographic">Cryptographic</option>
                    </select>
                </div>
            </div>
            
            <!-- RFID Reader Information -->
            <div class="card" style="background:#f8f9fa;">
                <h4><?= e(__('forensic.reader_info')) ?></h4>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.reader_freq')) ?> *</label>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-top:6px;">
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_frequencies[]" value="LF"> LF</label>
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_frequencies[]" value="HF"> HF</label>
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_frequencies[]" value="UHF"> UHF</label>
                    </div>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.reader_standards')) ?> *</label>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-top:6px;">
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_standards[]" value="ISO 14443"> ISO 14443</label>
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_standards[]" value="ISO 15693"> ISO 15693</label>
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_standards[]" value="EPC Gen2"> EPC Gen2</label>
                        <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" name="reader_standards[]" value="ISO 18000-6C"> ISO 18000-6C</label>
                    </div>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.auth_support')) ?> *</label>
                    <select name="auth_support" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="None">None</option>
                        <option value="AES">AES</option>
                        <option value="Mutual Auth">Mutual Auth</option>
                    </select>
                </div>
                
                <div style="margin-bottom:12px;">
                    <label><?= e(__('forensic.secure_logging')) ?> *</label>
                    <select name="secure_logging" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="Yes"><?= e(__('forensic.yes')) ?></option>
                        <option value="No"><?= e(__('forensic.no')) ?></option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Forensic Context -->
        <div class="card" style="background:#fff3cd;margin-bottom:20px;">
            <h4><?= e(__('forensic.context')) ?></h4>
            <div class="grid-2">
                <div>
                    <label><?= e(__('forensic.evidence_type')) ?> *</label>
                    <select name="evidence_type" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="Biological">Biological</option>
                        <option value="Digital">Digital</option>
                        <option value="Physical">Physical</option>
                        <option value="Chemical">Chemical</option>
                    </select>
                </div>
                
                <div>
                    <label><?= e(__('forensic.read_range')) ?> *</label>
                    <select name="read_range" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="Short">Short (&lt;10cm)</option>
                        <option value="Medium">Medium (~1m)</option>
                    </select>
                </div>
                
                <div>
                    <label><?= e(__('forensic.environment')) ?> *</label>
                    <select name="environment" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="Lab">Lab</option>
                        <option value="Crime Scene">Crime Scene</option>
                        <option value="Storage Room">Storage Room</option>
                    </select>
                </div>
                
                <div>
                    <label><?= e(__('forensic.sensitivity')) ?> *</label>
                    <select name="sensitivity_level" required>
                        <option value="">-- <?= e(__('samples.search_placeholder')) ?> --</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
            </div>
        </div>
        
        <button class="btn btn-blue" type="submit"><?= e(__('forensic.validate')) ?></button>
    </form>
</div>

<?php if ($result): ?>
<div class="card" style="margin-top:20px;">
    <h3><?= e(__('forensic.validation_result')) ?></h3>
    
    <div class="info-item">
        <span><?= e(__('forensic.suitability')) ?>:</span>
        <span class="status <?= $result['approved'] ? 'approved' : 'rejected' ?>">
            <?= $result['approved'] ? e(__('forensic.approved')) : e(__('forensic.rejected')) ?>
        </span>
    </div>
    
    <div class="info-item">
        <span><?= e(__('forensic.detected_type')) ?>:</span>
        <strong><?= e($result['rfid_type']) ?></strong>
    </div>
    
    <div class="info-item">
        <span><?= e(__('forensic.freq_validation')) ?>:</span>
        <span class="status <?= $result['frequency_validation'] ? 'approved' : 'rejected' ?>">
            <?= $result['frequency_validation'] ? 'Pass ✓' : 'Fail ✗' ?>
        </span>
    </div>
    
    <div class="info-item">
        <span><?= e(__('forensic.security_score')) ?>:</span>
        <strong><?= $result['security_score'] ?>/100</strong>
    </div>
    
    <?php if ($result['security_details']): ?>
    <div style="margin-top:12px;">
        <strong><?= e(__('forensic.security_details')) ?>:</strong>
        <ul style="padding-right:20px;margin-top:8px;">
            <?php foreach ($result['security_details'] as $detail): ?>
                <li><?= e($detail) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="info-item">
        <span><?= e(__('forensic.chain_compliance')) ?>:</span>
        <span class="status <?= $result['chain_compliant'] ? 'approved' : 'rejected' ?>">
            <?= $result['chain_compliant'] ? e(__('forensic.yes')) : e(__('forensic.no')) ?>
        </span>
    </div>
    
    <div class="info-item">
        <span><?= e(__('forensic.legal_risk')) ?>:</span>
        <span class="status <?= $result['legal_risk'] === 'Low' ? 'approved' : ($result['legal_risk'] === 'Medium' ? 'checked' : 'rejected') ?>">
            <?= e($result['legal_risk']) ?>
        </span>
    </div>
    
    <?php if (!$result['approved']): ?>
    <div style="margin-top:20px;padding:12px;background:#f8d7da;border-radius:8px;">
        <h4 style="color:#721c24;"><?= e(__('forensic.rejection_reasons')) ?>:</h4>
        <ul style="padding-right:20px;color:#721c24;">
            <?php foreach ($result['reasons'] as $reason): ?>
                <li><?= e($reason) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <?php if ($result['recommendations']): ?>
    <div style="margin-top:20px;padding:12px;background:#fff3cd;border-radius:8px;">
        <h4 style="color:#856404;"><?= e(__('forensic.recommendations_title')) ?>:</h4>
        <ul style="padding-right:20px;color:#856404;">
            <?php foreach ($result['recommendations'] as $rec): ?>
                <li><?= e($rec) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div style="margin-top:20px;padding:16px;background:#d1ecf1;border-radius:8px;">
        <h4 style="color:#0c5460;"><?= e(__('forensic.recommended_config_title')) ?>:</h4>
        <table style="margin-top:12px;width:100%;">
            <?php foreach ($result['recommended_config'] as $key => $value): ?>
                <tr>
                    <td style="padding:8px;border-bottom:1px solid #bee5eb;"><strong><?= e($key) ?></strong></td>
                    <td style="padding:8px;border-bottom:1px solid #bee5eb;"><?= e($value) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = __('forensic.title');
$active = 'forensic';
include __DIR__ . '/../partials/layout.php';
