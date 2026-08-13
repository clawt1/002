<?php
// /app/Views/projects/print.php - Rapport d'impression et Export PDF optimisé
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport de Projet - <?php echo htmlspecialchars($project['titre'], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            background-color: #ffffff;
            font-size: 11pt;
            line-height: 1.5;
            margin: 20mm 15mm 20mm 15mm;
        }
        h1 {
            font-size: 22pt;
            font-weight: 900;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 2mm;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 2mm;
        }
        h2 {
            font-size: 14pt;
            font-weight: 800;
            color: #1e293b;
            margin-top: 6mm;
            margin-bottom: 3mm;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1mm;
        }
        .meta-grid {
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 4mm;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 4mm;
            margin-bottom: 6mm;
        }
        .meta-item {
            font-size: 10pt;
        }
        .meta-label {
            font-weight: bold;
            color: #64748b;
        }
        .progress-bar-container {
            margin-top: 2mm;
            width: 100%;
            background-color: #e2e8f0;
            height: 6mm;
            border-radius: 3px;
            overflow: hidden;
        }
        .progress-bar-fill {
            background-color: #0284c7;
            height: 100%;
            text-align: right;
            padding-right: 2mm;
            color: white;
            font-size: 9pt;
            font-weight: bold;
            line-height: 6mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3mm;
            font-size: 9.5pt;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
            padding: 2.5mm;
        }
        td {
            border-bottom: 1px solid #e2e8f0;
            padding: 2.5mm;
            vertical-align: top;
        }
        .statut-badge {
            display: inline-block;
            padding: 0.5mm 2mm;
            border-radius: 2px;
            font-size: 8pt;
            font-weight: bold;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

    <!-- Controls to help download / print -->
    <div class="no-print" style="background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 4mm; margin-bottom: 6mm; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong style="color: #0f172a;">📄 Mode Impression / Export PDF</strong>
            <p style="margin: 1mm 0 0 0; font-size: 9pt; color: #64748b;">Configurez votre imprimante système pour exporter ou enregistrer directement au format PDF.</p>
        </div>
        <button onclick="window.print();" style="background-color: #0f172a; color: white; border: none; font-weight: bold; padding: 2.5mm 5mm; border-radius: 4px; cursor: pointer;">Lancer l'impression PDF</button>
    </div>

    <!-- Project Header -->
    <h1>RAPPORT DE PROJET</h1>

    <div class="meta-grid">
        <div class="meta-item">
            <span class="meta-label">Projet :</span> <?php echo htmlspecialchars($project['titre'], ENT_QUOTES, 'UTF-8'); ?><br>
            <span class="meta-label">Client :</span> <?php echo htmlspecialchars($project['client_nom'] ?: 'Interne', ENT_QUOTES, 'UTF-8'); ?><br>
            <span class="meta-label">Chef de projet :</span> <?php echo htmlspecialchars($project['owner_nom'] ?: 'Non spécifié', ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="meta-item">
            <span class="meta-label">Date d'édition :</span> <?php echo date('d/m/Y H:i'); ?><br>
            <span class="meta-label">Période :</span> Du <?php echo htmlspecialchars($project['date_debut'], ENT_QUOTES, 'UTF-8'); ?> au <?php echo htmlspecialchars($project['date_fin_prevue'], ENT_QUOTES, 'UTF-8'); ?><br>
            <span class="meta-label">Statut global :</span> <?php echo htmlspecialchars($project['statut'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    </div>

    <!-- Description -->
    <?php if ($project['description']): ?>
        <h2>Description du Projet</h2>
        <p style="font-size: 10pt; color: #334155; margin-bottom: 6mm;"><?php echo nl2br(htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8')); ?></p>
    <?php endif; ?>

    <!-- Progress Meter -->
    <h2>État d'Avancement</h2>
    <div class="progress-bar-container">
        <div class="progress-bar-fill" style="width: <?php echo $project['progress_pct']; ?>%;">
            <?php echo $project['progress_pct']; ?>% complété
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Tasks breakdown table -->
    <h2>Liste Exhaustive des Tâches</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Étape / Jalon</th>
                <th style="width: 35%;">Tâche</th>
                <th style="width: 15%;">Assigné à</th>
                <th style="width: 10%; text-align: right;">Est. (JH)</th>
                <th style="width: 15%; text-align: right;">Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tasks)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b; padding: 6mm;">Aucune tâche n'est rattachée à ce projet.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($t['step_titre'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                        <td>
                            <strong style="color: #0f172a;"><?php echo htmlspecialchars($t['titre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php if ($t['description']): ?>
                                <div style="font-size: 8.5pt; color: #64748b; margin-top: 1mm;"><?php echo htmlspecialchars($t['description'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($t['assigne_nom'] ?: 'Non assigné', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="text-align: right; font-weight: bold;"><?php echo $t['estimation_jours']; ?> JH</td>
                        <td style="text-align: right;">
                            <span class="statut-badge"><?php echo htmlspecialchars($t['statut'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Auto launch system print if triggered via print preview -->
    <script>
        // Optionnel: Décommenter pour forcer l'ouverture du dialogue d'impression directement
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
