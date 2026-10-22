<?php
$errors = [];
$values = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'ssn' => '',
    'address' => ''
];

function pdfLiteral(string $value): string
{
    $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $value);
    if ($encoded === false) {
        throw new RuntimeException('Could not encode customer information for the PDF.');
    }

    $encoded = preg_replace('/[^\x20-\x7E\x80-\xFF]/', ' ', $encoded);
    if ($encoded === null) {
        throw new RuntimeException('Could not format customer information for the PDF.');
    }

    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
}

function createCustomerPdf(array $values): string
{
    $lines = [
        ['Customer Information', 20],
        ['', 11],
        ['Full Name: ' . $values['full_name'], 11],
        ['Email Address: ' . $values['email'], 11],
        ['Phone Number: ' . $values['phone'], 11],
        ['Social Security Number: ' . substr($values['ssn'], 0, 3) . '-'
            . substr($values['ssn'], 3, 2) . '-' . substr($values['ssn'], 5), 11],
        ['Address:', 11],
    ];

    foreach (explode("\n", wordwrap($values['address'], 76, "\n", true)) as $line) {
        $lines[] = [$line, 11];
    }

    $content = "BT\n72 740 Td\n";
    foreach ($lines as [$line, $fontSize]) {
        if ($line !== '') {
            $content .= "/F1 {$fontSize} Tf\n(" . pdfLiteral($line) . ") Tj\n";
        }
        $content .= "0 -24 Td\n";
    }
    $content .= "ET\n";

    $objects = [
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "endstream",
    ];

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [0];
    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach (array_slice($offsets, 1) as $offset) {
        $pdf .= sprintf("%010d 00000 n \n", $offset);
    }

    return $pdf . "trailer\n<< /Size " . (count($objects) + 1)
        . " /Root 1 0 R >>\nstartxref\n" . $xrefOffset . "\n%%EOF";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $default) {
        $postedValue = $_POST[$field] ?? '';
        $values[$field] = is_string($postedValue) ? trim($postedValue) : '';
    }

    if ($values['full_name'] === '') {
        $errors[] = 'Please enter your full name.';
    } elseif (strlen($values['full_name']) > 120) {
        $errors[] = 'Your full name must be 120 characters or fewer.';
    }

    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($values['email']) > 254) {
        $errors[] = 'Your email address must be 254 characters or fewer.';
    }

    if ($values['phone'] === '') {
        $errors[] = 'Please enter your phone number.';
    } elseif (strlen($values['phone']) > 30) {
        $errors[] = 'Your phone number must be 30 characters or fewer.';
    }

    if (!preg_match('/^[0-9]{9}$/', $values['ssn'])) {
        $errors[] = 'Please enter all 9 digits of your Social Security number.';
    }

    if ($values['address'] === '') {
        $errors[] = 'Please enter your address.';
    } elseif (strlen($values['address']) > 500) {
        $errors[] = 'Your address must be 500 characters or fewer.';
    }

    if (!$errors) {
        $pdf = createCustomerPdf($values);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="customer-document.pdf"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, no-store, max-age=0');
        echo $pdf;
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3446e8">
    <link rel="manifest" href="/manifest.json">
    <title>Customer Information | Document Portal</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 30px 16px;
            background: #f5f7fb;
            color: #172033;
            font-family: Arial, sans-serif;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            z-index: 10;
            background: rgba(0, 0, 0, 0.1);
            pointer-events: none;
        }

        .container {
            max-width: 560px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(23, 32, 51, .08);
        }

        h1 { margin-top: 0; font-size: 28px; }
        p { color: #647084; line-height: 1.6; }

        label {
            display: block;
            margin: 18px 0 7px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 13px;
            border: 1px solid #ccd2df;
            border-radius: 8px;
            font: inherit;
        }

        .button {
            width: 100%;
            margin-top: 24px;
            padding: 15px;
            border: 0;
            border-radius: 8px;
            background: #3446e8;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .errors {
            padding: 12px 30px;
            color: #a32121;
            background: #fff0f0;
            border-radius: 8px;
        }

        .field-note {
            margin: 6px 0 0;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .container { padding: 22px; }
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Customer Information</h1>
        <p>
            Complete this form to generate your PDF document.
            Please check your information before continuing.
        </p>

        <?php if ($errors): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/api/customer-form.php" method="POST">
            <label for="full_name">Full Name</label>
            <input
                id="full_name"
                name="full_name"
                type="text"
                maxlength="120"
                autocomplete="name"
                value="<?= htmlspecialchars($values['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="email">Email Address</label>
            <input
                id="email"
                name="email"
                type="email"
                maxlength="254"
                autocomplete="email"
                value="<?= htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="phone">Phone Number</label>
            <input
                id="phone"
                name="phone"
                type="tel"
                maxlength="30"
                autocomplete="tel"
                value="<?= htmlspecialchars($values['phone'], ENT_QUOTES, 'UTF-8') ?>"
                required
            >

            <label for="ssn">Social Security Number (SSN)</label>
            <input
                id="ssn"
                name="ssn"
                type="password"
                inputmode="numeric"
                pattern="[0-9]{9}"
                maxlength="9"
                autocomplete="off"
                aria-describedby="ssn-note"
                title="Enter all 9 digits without dashes."
                required
            >
            <p class="field-note" id="ssn-note">
                Enter 9 digits without dashes. It will appear in the PDF and is not stored.
                Only submit over a secure HTTPS connection.
            </p>

            <label for="address">Address</label>
            <textarea
                id="address"
                name="address"
                maxlength="500"
                autocomplete="street-address"
                rows="3"
                required
            ><?= htmlspecialchars($values['address'], ENT_QUOTES, 'UTF-8') ?></textarea>

            <button class="button" type="submit">
                Generate My PDF
            </button>
        </form>
    </main>
</body>
</html>
