# Ticket Flow Twig

A simple ticket management system built with PHP and the Twig templating engine. This application allows users to create, edit, and delete support tickets with a clean, responsive interface.

## Features

- **User Authentication**: Secure login and signup functionality.
- **Ticket Management**: Create, view, edit, and delete tickets.
- **Dashboard**: Overview of ticket statistics (total, open, resolved).
- **Responsive Design**: Mobile-friendly UI with modern styling.
- **Auto-Hiding Messages**: Success and error messages fade out after 5 seconds.
- **Confirmation Dialogs**: Prevents accidental deletions.

## Requirements

- PHP 8.0 or higher
- Composer (for dependency management)
- A web server (e.g., Apache, Nginx) or PHP's built-in server

## Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/yourusername/ticket-flow-twig.git
   cd ticket-flow-twig
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Start the server**:
   - Using PHP's built-in server:
     ```bash
     php -S localhost:8000 -t public
     ```
   - Or configure your web server to serve the `public` directory.

4. **Access the application**:
   Open your browser and navigate to `http://localhost:8000` (or your configured URL).

## Usage

1. **Sign Up**: Create a new account or log in with existing credentials.
2. **Dashboard**: View ticket statistics after logging in.
3. **Manage Tickets**:
   - Click "Create New Ticket" to add a ticket.
   - Use the Edit button to modify existing tickets.
   - Use the Delete button to remove tickets (with confirmation).
4. **Logout**: Securely log out from the ticket management page.

## Project Structure

- `public/`: Web root directory containing `index.php` and static assets.
- `src/`: Source code, including authentication logic.
- `templates/`: Twig templates for views.
- `vendor/`: Composer dependencies (auto-generated).
- `data/`: JSON files for storing user and ticket data (created automatically).

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request with your changes.

## License

This project is licensed under the MIT License. See the LICENSE file for details.
