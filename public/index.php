<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Auth.php';

Auth::init();

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');
$twig = new \Twig\Environment($loader);

switch ($path) {
    case '/':
        echo $twig->render('home.twig');
        break;
    case '/auth/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                Auth::login($_POST['email'], $_POST['password']);
                header('Location: /dashboard');
                exit;
            } catch (Exception $e) {
                echo $twig->render('login.twig', ['error' => $e->getMessage()]);
            }
        } else {
            echo $twig->render('login.twig');
        }
        break;
    case '/auth/signup':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if ($_POST['password'] !== $_POST['confirmPassword']) {
                    throw new Exception('Passwords do not match');
                }
                Auth::signup($_POST['email'], $_POST['password']);
                header('Location: /dashboard');
                exit;
            } catch (Exception $e) {
                echo $twig->render('signup.twig', ['error' => $e->getMessage()]);
            }
        } else {
            echo $twig->render('signup.twig');
        }
        break;
    case '/auth/logout':
        Auth::logout();
        header('Location: /');
        exit;
    case '/dashboard':
        if (!Auth::isAuthenticated()) {
            header('Location: /auth/login');
            exit;
        }
        // Load stats
        $ticketsFile = __DIR__ . '/../data/tickets.json';
        if (!file_exists(dirname($ticketsFile))) {
            mkdir(dirname($ticketsFile), 0777, true);
        }
        $tickets = json_decode(file_get_contents($ticketsFile), true) ?: [];
        $stats = [
            'total' => count($tickets),
            'open' => count(array_filter($tickets, fn($t) => $t['status'] === 'open')),
            'resolved' => count(array_filter($tickets, fn($t) => $t['status'] === 'closed'))
        ];
        echo $twig->render('dashboard.twig', ['stats' => $stats]);
        break;
    case '/tickets':
        if (!Auth::isAuthenticated()) {
            header('Location: /auth/login');
            exit;
        }
        $ticketsFile = __DIR__ . '/../data/tickets.json';
        if (!file_exists(dirname($ticketsFile))) {
            mkdir(dirname($ticketsFile), 0777, true);
        }
        $tickets = json_decode(file_get_contents($ticketsFile), true) ?: [];

        $show_form = isset($_GET['create']) && $_GET['create'] == '1';
        $edit_ticket = null;
        if (isset($_GET['edit'])) {
            foreach ($tickets as $ticket) {
                if ($ticket['id'] === $_GET['edit']) {
                    $edit_ticket = $ticket;
                    $show_form = true;
                    break;
                }
            }
        }
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['delete_id'])) {
                // Delete ticket
                $deleteId = $_POST['delete_id'];
                $tickets = array_filter($tickets, fn($t) => $t['id'] !== $deleteId);
                file_put_contents($ticketsFile, json_encode(array_values($tickets)));
                $success = 'Ticket deleted successfully!';
            } elseif (isset($_POST['edit_id'])) {
                // Update ticket
                $editId = $_POST['edit_id'];
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $priority = $_POST['priority'] ?? 'medium';
                $status = $_POST['status'] ?? 'open';

                if (empty($title) || empty($status)) {
                    $error = 'Title and status are required.';
                } else {
                    foreach ($tickets as &$ticket) {
                        if ($ticket['id'] === $editId) {
                            $ticket['title'] = $title;
                            $ticket['description'] = $description;
                            $ticket['priority'] = $priority;
                            $ticket['status'] = $status;
                            $ticket['updated_at'] = date('c');
                            break;
                        }
                    }
                    file_put_contents($ticketsFile, json_encode($tickets));
                    $success = 'Ticket updated successfully!';
                    $show_form = false;
                    $edit_ticket = null;
                }
            } else {
                // Create ticket
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $priority = $_POST['priority'] ?? 'medium';
                $status = $_POST['status'] ?? 'open';

                if (empty($title) || empty($status)) {
                    $error = 'Title and status are required.';
                } else {
                    $newTicket = [
                        'id' => uniqid(),
                        'title' => $title,
                        'description' => $description,
                        'priority' => $priority,
                        'status' => $status,
                        'created_at' => date('c'),
                        'updated_at' => date('c')
                    ];
                    $tickets[] = $newTicket;
                    file_put_contents($ticketsFile, json_encode($tickets));
                    $success = 'Ticket created successfully!';
                    $show_form = false;
                }
            }
        }

        foreach ($tickets as &$ticket) {
            $ticket['status_color'] = match($ticket['status']) {
                'open' => '#4caf50',
                'in_progress' => '#ff9800',
                'closed' => '#f44336',
                default => '#9e9e9e'
            };
            $ticket['priority_color'] = match($ticket['priority']) {
                'low' => '#2196f3',
                'medium' => '#ff9800',
                'high' => '#f44336',
                default => '#9e9e9e'
            };
        }
        echo $twig->render('ticket_management.twig', [
            'tickets' => $tickets,
            'show_form' => $show_form,
            'edit_ticket' => $edit_ticket,
            'error' => $error,
            'success' => $success
        ]);
        break;
    default:
        http_response_code(404);
        echo '404 Not Found';
        break;
}
