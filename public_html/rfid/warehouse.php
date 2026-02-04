<?php
ob_start();
require_once __DIR__ . '/../config/auth.php';
require_login();

$active = 'warehouse';
$validation_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get tag information
    $tag_type = $_POST['tag_type'] ?? '';
    $freq_band = $_POST['freq_band'] ?? '';
    $freq_value = $_POST['freq_value'] ?? '';
    $protocol = $_POST['protocol'] ?? '';
    $read_range = $_POST['read_range'] ?? '';
    $bulk_read = $_POST['bulk_read'] ?? '';
    $form_factor = $_POST['form_factor'] ?? '';
    
    // Get reader information
    $reader_freq = $_POST['reader_freq'] ?? [];
    $reader_protocols = $_POST['reader_protocols'] ?? [];
    $max_concurrent = intval($_POST['max_concurrent'] ?? 0);
    $reader_type = $_POST['reader_type'] ?? '';
    $anti_collision = $_POST['anti_collision'] ?? '';
    
    // Get warehouse context
    $warehouse_size = $_POST['warehouse_size'] ?? '';
    $product_type = $_POST['product_type'] ?? '';
    $storage_method = $_POST['storage_method'] ?? '';
    $operation = $_POST['operation'] ?? '';
    $accuracy_level = $_POST['accuracy_level'] ?? '';
    
    // Initialize validation
    $is_approved = true;
    $rejection_reasons = [];
    $recommendations = [];
    
    // Detect RFID Type
    $rfid_type = '';
    if ($freq_band === 'LF') {
        $rfid_type = 'Low Frequency RFID (125 kHz)';
    } elseif ($freq_band === 'HF') {
        $rfid_type = 'High Frequency RFID (13.56 MHz)';
    } elseif ($freq_band === 'UHF') {
        $rfid_type = 'Ultra High Frequency RFID (860-960 MHz)';
    }
    
    // VALIDATION RULE 1: Must support bulk reading for inventory
    if ($bulk_read !== 'Yes') {
        $is_approved = false;
        $rejection_reasons[] = __('warehouse.no_bulk_read');
        $recommendations[] = __('warehouse.rec_bulk_read');
    }
    
    // VALIDATION RULE 2: LF prohibited for large-scale operations
    if ($freq_band === 'LF' && in_array($warehouse_size, ['Medium', 'Large'])) {
        $is_approved = false;
        $rejection_reasons[] = __('warehouse.lf_large_scale');
        $recommendations[] = __('warehouse.rec_use_uhf');
    }
    
    // VALIDATION RULE 3: UHF EPC Gen2 preferred for medium/large warehouses
    if (in_array($warehouse_size, ['Medium', 'Large']) && 
        !($freq_band === 'UHF' && in_array($protocol, ['EPC Gen2', 'ISO 18000-6C']))) {
        $recommendations[] = __('warehouse.rec_uhf_epc');
    }
    
    // VALIDATION RULE 4: Anti-collision required for bulk reading
    if ($bulk_read === 'Yes' && $anti_collision !== 'Yes') {
        $is_approved = false;
        $rejection_reasons[] = __('warehouse.no_anti_collision');
        $recommendations[] = __('warehouse.rec_anti_collision');
    }
    
    // VALIDATION RULE 5: Metal-mount tags required for metal products
    if ($product_type === 'Metal Products' && $form_factor !== 'Metal-Mount') {
        $is_approved = false;
        $rejection_reasons[] = __('warehouse.no_metal_mount');
        $recommendations[] = __('warehouse.rec_metal_mount');
    }
    
    // Frequency Band Validation
    $freq_validation = 'Pass';
    if (!in_array($freq_band, $reader_freq)) {
        $freq_validation = 'Fail';
        $is_approved = false;
        $rejection_reasons[] = __('warehouse.freq_mismatch');
        $recommendations[] = __('warehouse.rec_freq_match');
    }
    
    // Protocol Compatibility
    $protocol_compatible = in_array($protocol, $reader_protocols);
    if (!$protocol_compatible) {
        $is_approved = false;
        $rejection_reasons[] = __('warehouse.protocol_mismatch');
        $recommendations[] = __('warehouse.rec_protocol_match');
    }
    
    // Inventory Read Efficiency
    $read_efficiency = 'Low';
    $efficiency_score = 0;
    
    if ($bulk_read === 'Yes') $efficiency_score += 30;
    if ($anti_collision === 'Yes') $efficiency_score += 30;
    if ($freq_band === 'UHF') $efficiency_score += 25;
    if (in_array($protocol, ['EPC Gen2', 'ISO 18000-6C'])) $efficiency_score += 15;
    
    if ($efficiency_score >= 70) {
        $read_efficiency = 'High';
    } elseif ($efficiency_score >= 40) {
        $read_efficiency = 'Medium';
    }
    
    // Reader-Tag Compatibility
    $reader_tag_compatible = $protocol_compatible && ($freq_validation === 'Pass');
    
    // Operational Risk Level
    $risk_level = 'Low';
    $risk_factors = 0;
    
    if ($accuracy_level === 'Critical' && $read_efficiency !== 'High') $risk_factors += 2;
    if ($product_type === 'Metal Products' && $form_factor !== 'Metal-Mount') $risk_factors += 2;
    if ($product_type === 'Liquids' && $freq_band === 'UHF') $risk_factors += 1;
    if ($warehouse_size === 'Large' && $read_range === 'Short') $risk_factors += 1;
    if (!$protocol_compatible) $risk_factors += 2;
    if ($bulk_read === 'No' && in_array($operation, ['Receiving', 'Shipping', 'Cycle Count'])) $risk_factors += 1;
    
    if ($risk_factors >= 4) {
        $risk_level = 'High';
    } elseif ($risk_factors >= 2) {
        $risk_level = 'Medium';
    }
    
    // Recommended Configuration
    $recommended_config = [
        'tag_type' => 'Passive',
        'frequency' => 'UHF (860-960 MHz)',
        'protocol' => 'EPC Gen2 / ISO 18000-6C',
        'bulk_read' => 'Enabled',
        'anti_collision' => 'Enabled',
        'form_factor' => $product_type === 'Metal Products' ? 'Metal-Mount' : 'Label',
        'reader_type' => $warehouse_size === 'Large' ? 'Fixed Gate' : 'Handheld',
        'max_concurrent' => $warehouse_size === 'Large' ? '200+' : '50+',
    ];
    
    // Additional recommendations based on context
    if ($product_type === 'Liquids') {
        $recommendations[] = __('warehouse.rec_hf_liquids');
        $recommended_config['frequency'] = 'HF (13.56 MHz)';
        $recommended_config['protocol'] = 'ISO 15693';
    }
    
    if ($accuracy_level === 'Critical') {
        $recommendations[] = __('warehouse.rec_critical_accuracy');
    }
    
    $validation_result = [
        'is_approved' => $is_approved,
        'rfid_type' => $rfid_type,
        'freq_validation' => $freq_validation,
        'read_efficiency' => $read_efficiency,
        'efficiency_score' => $efficiency_score,
        'reader_tag_compatible' => $reader_tag_compatible,
        'risk_level' => $risk_level,
        'risk_factors' => $risk_factors,
        'rejection_reasons' => $rejection_reasons,
        'recommendations' => $recommendations,
        'recommended_config' => $recommended_config,
    ];
}
?>

<div class="container">
    <div class="page-header">
        <h2><?= e(__('warehouse.title')) ?></h2>
        <p><?= e(__('warehouse.description')) ?></p>
    </div>

    <form method="post" class="card" style="max-width: 1200px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- RFID Tag Specifications -->
            <div>
                <h3 style="margin-bottom: 15px; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">
                    <?= e(__('warehouse.tag_specs')) ?>
                </h3>
                
                <div class="form-group">
                    <label><?= e(__('warehouse.tag_type')) ?> *</label>
                    <select name="tag_type" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Passive">Passive</option>
                        <option value="Semi-Passive">Semi-Passive</option>
                        <option value="Active">Active</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.freq_band')) ?> *</label>
                    <select name="freq_band" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="LF">LF (Low Frequency)</option>
                        <option value="HF">HF (High Frequency)</option>
                        <option value="UHF">UHF (Ultra High Frequency)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.freq_value')) ?> *</label>
                    <select name="freq_value" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="125kHz">125 kHz</option>
                        <option value="13.56MHz">13.56 MHz</option>
                        <option value="860-960MHz">860-960 MHz</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.protocol')) ?> *</label>
                    <select name="protocol" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="ISO 14443">ISO 14443</option>
                        <option value="ISO 15693">ISO 15693</option>
                        <option value="EPC Gen2">EPC Gen2</option>
                        <option value="ISO 18000-6C">ISO 18000-6C</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.read_range')) ?> *</label>
                    <select name="read_range" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Short">Short (&lt;1m)</option>
                        <option value="Medium">Medium (1-5m)</option>
                        <option value="Long">Long (&gt;5m)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.bulk_read')) ?> *</label>
                    <select name="bulk_read" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Yes"><?= e(__('warehouse.yes')) ?></option>
                        <option value="No"><?= e(__('warehouse.no')) ?></option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.form_factor')) ?> *</label>
                    <select name="form_factor" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Label">Label</option>
                        <option value="Hard Tag">Hard Tag</option>
                        <option value="Metal-Mount">Metal-Mount</option>
                    </select>
                </div>
            </div>

            <!-- RFID Reader Specifications -->
            <div>
                <h3 style="margin-bottom: 15px; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">
                    <?= e(__('warehouse.reader_specs')) ?>
                </h3>

                <div class="form-group">
                    <label><?= e(__('warehouse.reader_freq')) ?> *</label>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: normal;">
                            <input type="checkbox" name="reader_freq[]" value="LF"> LF (125 kHz)
                        </label>
                        <label style="font-weight: normal;">
                            <input type="checkbox" name="reader_freq[]" value="HF"> HF (13.56 MHz)
                        </label>
                        <label style="font-weight: normal;">
                            <input type="checkbox" name="reader_freq[]" value="UHF"> UHF (860-960 MHz)
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.reader_protocols')) ?> *</label>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-weight: normal;">
                            <input type="checkbox" name="reader_protocols[]" value="ISO 14443"> ISO 14443
                        </label>
                        <label style="font-weight: normal;">
                            <input type="checkbox" name="reader_protocols[]" value="ISO 15693"> ISO 15693
                        </label>
                        <label style="font-weight: normal;">
                            <input type="checkbox" name="reader_protocols[]" value="EPC Gen2"> EPC Gen2
                        </label>
                        <label style="font-weight: normal;">
                            <input type="checkbox" name="reader_protocols[]" value="ISO 18000-6C"> ISO 18000-6C
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.max_concurrent')) ?> *</label>
                    <input type="number" name="max_concurrent" min="1" required>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.reader_type')) ?> *</label>
                    <select name="reader_type" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Handheld">Handheld</option>
                        <option value="Fixed Gate">Fixed Gate</option>
                        <option value="Conveyor Mounted">Conveyor Mounted</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.anti_collision')) ?> *</label>
                    <select name="anti_collision" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Yes"><?= e(__('warehouse.yes')) ?></option>
                        <option value="No"><?= e(__('warehouse.no')) ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Warehouse Context -->
        <div style="margin-top: 30px;">
            <h3 style="margin-bottom: 15px; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">
                <?= e(__('warehouse.context')) ?>
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label><?= e(__('warehouse.warehouse_size')) ?> *</label>
                    <select name="warehouse_size" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Small">Small</option>
                        <option value="Medium">Medium</option>
                        <option value="Large">Large</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.product_type')) ?> *</label>
                    <select name="product_type" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="General Goods">General Goods</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Liquids">Liquids</option>
                        <option value="Metal Products">Metal Products</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.storage_method')) ?> *</label>
                    <select name="storage_method" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Shelves">Shelves</option>
                        <option value="Pallets">Pallets</option>
                        <option value="Containers">Containers</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.operation')) ?> *</label>
                    <select name="operation" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Receiving">Receiving</option>
                        <option value="Picking">Picking</option>
                        <option value="Shipping">Shipping</option>
                        <option value="Cycle Count">Cycle Count</option>
                    </select>
                </div>

                <div class="form-group">
                    <label><?= e(__('warehouse.accuracy_level')) ?> *</label>
                    <select name="accuracy_level" required>
                        <option value="">-- <?= e(__('warehouse.select')) ?> --</option>
                        <option value="Standard">Standard</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 20px; width: 100%;">
            <?= e(__('warehouse.validate')) ?>
        </button>
    </form>

    <?php if ($validation_result): ?>
    <div class="card" style="margin-top: 30px; max-width: 1200px; margin-left: auto; margin-right: auto;">
        <h3 style="margin-bottom: 20px; color: #2c3e50;">
            <?= e(__('warehouse.validation_result')) ?>
        </h3>

        <!-- Suitability Status -->
        <div style="padding: 20px; border-radius: 8px; margin-bottom: 20px; background: <?= $validation_result['is_approved'] ? '#d4edda' : '#f8d7da' ?>; border: 2px solid <?= $validation_result['is_approved'] ? '#28a745' : '#dc3545' ?>;">
            <h4 style="margin: 0; color: <?= $validation_result['is_approved'] ? '#155724' : '#721c24' ?>;">
                <?= e(__('warehouse.suitability')) ?>: 
                <?= $validation_result['is_approved'] ? e(__('warehouse.approved')) : e(__('warehouse.rejected')) ?>
            </h4>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <p><strong><?= e(__('warehouse.detected_type')) ?>:</strong> <?= e($validation_result['rfid_type']) ?></p>
                <p><strong><?= e(__('warehouse.freq_validation')) ?>:</strong> 
                    <span style="color: <?= $validation_result['freq_validation'] === 'Pass' ? '#28a745' : '#dc3545' ?>;">
                        <?= e($validation_result['freq_validation']) ?>
                    </span>
                </p>
                <p><strong><?= e(__('warehouse.read_efficiency')) ?>:</strong> 
                    <span style="color: <?= $validation_result['read_efficiency'] === 'High' ? '#28a745' : ($validation_result['read_efficiency'] === 'Medium' ? '#ffc107' : '#dc3545') ?>;">
                        <?= e($validation_result['read_efficiency']) ?> (<?= $validation_result['efficiency_score'] ?>/100)
                    </span>
                </p>
            </div>
            <div>
                <p><strong><?= e(__('warehouse.compatibility')) ?>:</strong> 
                    <span style="color: <?= $validation_result['reader_tag_compatible'] ? '#28a745' : '#dc3545' ?>;">
                        <?= $validation_result['reader_tag_compatible'] ? e(__('warehouse.compatible')) : e(__('warehouse.not_compatible')) ?>
                    </span>
                </p>
                <p><strong><?= e(__('warehouse.risk_level')) ?>:</strong> 
                    <span style="color: <?= $validation_result['risk_level'] === 'Low' ? '#28a745' : ($validation_result['risk_level'] === 'Medium' ? '#ffc107' : '#dc3545') ?>;">
                        <?= e($validation_result['risk_level']) ?>
                    </span>
                </p>
            </div>
        </div>

        <?php if (!empty($validation_result['rejection_reasons'])): ?>
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
            <h4 style="margin-top: 0; color: #856404;"><?= e(__('warehouse.rejection_reasons')) ?>:</h4>
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($validation_result['rejection_reasons'] as $reason): ?>
                <li><?= e($reason) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($validation_result['recommendations'])): ?>
        <div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border-left: 4px solid #17a2b8;">
            <h4 style="margin-top: 0; color: #0c5460;"><?= e(__('warehouse.recommendations_title')) ?>:</h4>
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($validation_result['recommendations'] as $rec): ?>
                <li><?= e($rec) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Recommended Configuration -->
        <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border: 2px solid #2196F3; border-radius: 8px;">
            <h4 style="margin-top: 0; color: #1976D2;"><?= e(__('warehouse.recommended_config_title')) ?>:</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <?php foreach ($validation_result['recommended_config'] as $key => $value): ?>
                <p style="margin: 5px 0;">
                    <strong><?= e(ucwords(str_replace('_', ' ', $key))) ?>:</strong> <?= e($value) ?>
                </p>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = __('warehouse.title');
include __DIR__ . '/../partials/layout.php';
?>
