<?php
session_start();

// Redirect if no success data
if (empty($_SESSION['reservation_success']) || empty($_SESSION['reservation_codes'])) {
    header("Location: cart.php");
    exit();
}

include 'index/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservation Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-2xl mx-auto mt-10 p-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden" id="reservation-success-modal">
            <!-- Success Header -->
            <div class="bg-green-50 p-6 border-b border-green-100">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-2 mr-4">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-green-700">Reservation Successful!</h2>
                        <p class="text-green-600">Your items have been successfully reserved.</p>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Important Instructions:</h3>
                    <ol class="list-decimal ml-5 space-y-2 text-gray-600">
                        <li>
                            Please save your reservation code<?= count($_SESSION['reservation_codes']) > 1 ? 's' : '' ?> below
                        </li>
                        <li>
                            Present this code to the admin when borrowing items
                        </li>
                        <li>
                            Without this code, you cannot claim your reserved items
                        </li>
                        <li>
                            <?php if (count($_SESSION['reservation_codes']) > 1): ?>
                                Name for multiple product checkouts:
                                <span class="font-semibold text-gray-800">
                                    <?= htmlspecialchars($_SESSION['reservation_success']['requested_by'] ?? '') ?>
                                </span>
                            <?php else: ?>
                                Name who requested it:
                                <span class="font-semibold text-gray-800">
                                    <?= htmlspecialchars($_SESSION['reservation_success']['requested_by'] ?? '') ?>
                                </span>
                            <?php endif; ?>
                        </li>
                        <li>
                            Reservation Date & Timeslot: 
                            <span class="font-semibold text-gray-800">
                                <?php
                                // Fetch and display the reservation date(s) and timeslot(s) for the reserved items
                                if (!empty($_SESSION['reservation_codes'])) {
                                    include 'config.php';
                                    $codes = $_SESSION['reservation_codes'];
                                    $placeholders = implode(',', array_fill(0, count($codes), '?'));
                                    $types = str_repeat('s', count($codes));
                                    $stmt = $conn->prepare("SELECT DISTINCT reservation_date, reservation_timeslot FROM reservation WHERE reservation_code IN ($placeholders)");
                                    $stmt->bind_param($types, ...$codes);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $dateSlots = [];
                                    while ($row = $result->fetch_assoc()) {
                                        $date = htmlspecialchars($row['reservation_date']);
                                        $slot = htmlspecialchars($row['reservation_timeslot']);
                                        $dateSlots[] = $date . ($slot ? " ($slot)" : "");
                                    }
                                    echo implode(', ', $dateSlots);
                                    $stmt->close();
                                } else {
                                    echo htmlspecialchars($_SESSION['reservation_success']['reservation_date']);
                                }
                                ?>
                            </span>
                        </li>
                    </ol>
                </div>
                <!-- Reservation Codes -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-gray-700 mb-3">Your Reservation Code<?= count($_SESSION['reservation_codes']) > 1 ? 's' : '' ?>:</h3>
                    <?php foreach ($_SESSION['reservation_codes'] as $code): ?>
                        <div class="bg-white px-4 py-2 rounded border font-mono text-lg mb-2">
                            <?= htmlspecialchars($code) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- Product Names List -->
                <?php
                function printProductNames($products) {
                    echo '<div class="mb-6">';
                    echo '<h3 class="font-semibold text-gray-700 mb-3">Reserved Product' . (is_array($products) && count($products) > 1 ? 's' : '') . ':</h3>';
                    if (!$products || !is_array($products) || count($products) === 0) {
                        echo '<div class="text-gray-500 italic">No products reserved.</div>';
                    } else {
                        echo '<ul class="list-disc ml-6 text-gray-800">';
                        foreach ($products as $prod) {
                            echo '<li>' . htmlspecialchars($prod['product_name']) . '</li>';
                        }
                        echo '</ul>';
                    }
                    echo '</div>';
                }
                printProductNames($_SESSION['reservation_success']['products'] ?? []);
                ?>
                <!-- Print and Home Buttons -->
                <div class="flex gap-4 no-print">
                    <button onclick="printReservationSuccess()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Print
                    </button>
                    <a href="index.php" 
                       class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        Go to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script>
    function printReservationSuccess() {
        printElement('reservation-success-modal');
    }
    function printElement(elementId) {
        var printContents = document.getElementById(elementId).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }
    </script>
</body>
</html>

<?php
// Clear the session variables after displaying
unset($_SESSION['reservation_success']);
unset($_SESSION['reservation_codes']);
?>