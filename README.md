# Immotep - Property Management System

Immotep is a web application built with Symfony that allows property owners and managers to efficiently track and manage their apartments, tenants, and rental payments.

## Features

- **Property Management**: Add, edit, and track information about your real estate properties
- **Tenant Management**: Manage contact information and details of your tenants
- **Payment Tracking**: Record and monitor rent payments, generate receipts
- **Dashboard**: Quickly visualize key indicators (occupancy rate, late payments, etc.)
- **Authentication System**: Secure access to the application via login and registration

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL/MariaDB
- Symfony CLI (recommended)

### Installation Steps

1. Clone the repository
   ```bash
   git clone git@github.com:syrb/immotep.git
   cd immotep
   ```

2. Install dependencies
   ```bash
   composer install
   ```

3. Configure the database in `.env.local` file
   ```
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/immotep?serverVersion=8.0"
   ```

4. Create the database and run migrations
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. Start the development server
   ```bash
   symfony server:start
   ```

6. Access the application at [http://localhost:8000](http://localhost:8000)

## Technologies Used

- **Symfony 6.4**: Robust and flexible PHP framework
- **Bootstrap 5**: CSS framework for responsive and modern interface
- **MySQL**: Relational database management system
- **FontAwesome**: Vector icon library

## Project Structure

- `src/Controller/`: Controllers for each section of the application
- `src/Entity/`: Doctrine entities (Apartment, Tenant, Payment, User, etc.)
- `src/Form/`: Form types for creating and editing data
- `src/Repository/`: Repositories for custom queries
- `templates/`: Twig templates organized by section

## Contributing

Contributions are welcome! Feel free to submit pull requests or report issues via GitHub issues.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License. See the `LICENSE` file for more details.
