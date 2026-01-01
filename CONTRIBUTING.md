# Contributing to EduTrack

Thank you for your interest in contributing to EduTrack! This document provides guidelines and instructions for contributing to the project.

---

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [How to Contribute](#how-to-contribute)
- [Development Workflow](#development-workflow)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Suggesting Features](#suggesting-features)
- [Contact](#contact)

---

## Code of Conduct

By participating in this project, you agree to:

- Be respectful and inclusive
- Accept constructive criticism gracefully
- Focus on what is best for the project and community
- Show empathy towards other community members

---

## Getting Started

### Prerequisites

Before you begin, ensure you have:

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Git
- A code editor (VS Code, PHPStorm, etc.)

### Setting Up Your Development Environment

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/YOUR-USERNAME/edutrack.git
   cd edutrack
   ```
3. **Install dependencies:**
   ```bash
   composer install
   ```
4. **Create a `.env` file** from `.env.example`:
   ```bash
   cp .env.example .env
   ```
5. **Configure your database** in `.env`
6. **Import the database schema:**
   ```bash
   mysql -u root -p edutrack < database/schema.sql
   ```
7. **Set up your virtual host** to point to the `public/` directory

---

## How to Contribute

There are many ways to contribute to EduTrack:

- **Report bugs** you encounter
- **Suggest new features** or improvements
- **Write or improve documentation**
- **Submit bug fixes** or new features
- **Review pull requests**
- **Improve test coverage**

---

## Development Workflow

1. **Create a new branch** for your work:
   ```bash
   git checkout -b feature/your-feature-name
   # or
   git checkout -b bugfix/issue-number-description
   ```

2. **Make your changes** following our coding standards

3. **Test your changes** thoroughly:
   - Test manually in the browser
   - Run automated tests if available
   - Check for PHP errors and warnings

4. **Commit your changes** with clear messages

5. **Push to your fork:**
   ```bash
   git push origin feature/your-feature-name
   ```

6. **Submit a pull request** to the main repository

---

## Coding Standards

### PHP Standards

- Follow **PSR-12** coding style standard
- Use **meaningful variable and function names**
- Add **PHPDoc comments** for all functions and classes:
  ```php
  /**
   * Mark student attendance for a session
   *
   * @param int $studentId The student's user ID
   * @param int $sessionId The session ID
   * @return bool True on success, false on failure
   */
  function markAttendance($studentId, $sessionId) {
      // Implementation
  }
  ```
- Use **type hints** where possible (PHP 7.4+)
- Handle **errors gracefully** with try-catch blocks
- **Never expose sensitive data** in error messages

### Security Best Practices

- Always use **prepared statements** for database queries
- **Validate and sanitize** all user input
- Use **htmlspecialchars()** when outputting user data
- Implement **CSRF protection** for all forms
- Use **password_hash()** and **password_verify()** for passwords
- Never store passwords in plain text
- Validate file uploads properly

### Database Queries

**Good:**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
```

**Bad:**
```php
$user = $pdo->query("SELECT * FROM users WHERE id = $userId")->fetch();
```

### HTML/CSS

- Use **Bootstrap 5** classes for styling
- Keep **inline styles minimal** - prefer CSS classes
- Ensure **responsive design** (mobile-friendly)
- Use **semantic HTML5** tags (main, nav, section, article)
- Add **ARIA labels** for accessibility

### JavaScript

- Use **vanilla JavaScript** or jQuery if needed
- Keep JavaScript **CSP-compliant** (no inline event handlers)
- Use **async/await** for asynchronous operations
- Handle **errors properly** with try-catch
- Add **comments** for complex logic

---

## Commit Guidelines

### Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- **feat:** New feature
- **fix:** Bug fix
- **docs:** Documentation changes
- **style:** Code style changes (formatting, no logic change)
- **refactor:** Code refactoring
- **test:** Adding or updating tests
- **chore:** Maintenance tasks

### Examples

```
feat(attendance): add QR code generation for sessions

Implemented QR code generation using PHP QR Code library.
Each session now generates a unique QR code that expires
after the session ends.

Closes #42
```

```
fix(admin): resolve student deletion cascade issue

Fixed bug where deleting a student didn't cascade to
enrollments table, causing foreign key constraint errors.

Fixes #38
```

---

## Pull Request Process

1. **Update documentation** if you've changed functionality
2. **Ensure all tests pass** (if applicable)
3. **Update the CHANGELOG.md** with your changes
4. **Reference any related issues** in the PR description
5. **Request review** from maintainers
6. **Address review feedback** promptly
7. **Squash commits** if requested before merging

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
How did you test these changes?

## Related Issues
Closes #issue_number

## Screenshots (if applicable)
Add screenshots here

## Checklist
- [ ] My code follows the project's coding standards
- [ ] I have commented my code where necessary
- [ ] I have updated the documentation
- [ ] My changes generate no new warnings
- [ ] I have tested my changes thoroughly
```

---

## Reporting Bugs

### Before Submitting a Bug Report

- **Check existing issues** to avoid duplicates
- **Verify the bug** in the latest version
- **Collect information** about your environment

### Bug Report Template

```markdown
**Description:**
A clear description of the bug

**Steps to Reproduce:**
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

**Expected Behavior:**
What you expected to happen

**Actual Behavior:**
What actually happened

**Screenshots:**
Add screenshots if applicable

**Environment:**
- PHP Version: [e.g., 7.4]
- MySQL Version: [e.g., 5.7]
- Browser: [e.g., Chrome 96]
- OS: [e.g., Windows 10]

**Additional Context:**
Any other relevant information
```

---

## Suggesting Features

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
A clear description of the problem

**Describe the solution you'd like:**
What you want to happen

**Describe alternatives you've considered:**
Other solutions you've thought about

**Additional context:**
Any other relevant information, mockups, or examples
```

---

## Code Review Process

All submissions require review before merging:

1. **Automated checks** run on every PR
2. **At least one maintainer** must approve
3. **All review comments** must be addressed
4. **Conflicts must be resolved** before merging

---

## Project Structure Guidelines

When adding new files, follow the existing structure:

```
edutrack/
├── auth/                 # Authentication pages
├── pages/
│   ├── admin/           # Admin dashboard pages
│   ├── lecturer/        # Lecturer dashboard pages
│   └── student/         # Student dashboard pages
├── includes/            # Shared PHP includes
├── controllers/         # Business logic controllers
├── public/              # Public assets (CSS, JS, images)
├── config/              # Configuration files
└── tests/               # Test files
```

---

## Testing Guidelines

- **Manual testing** is required for all changes
- Write **unit tests** for new functions when possible
- Test on **multiple browsers** (Chrome, Firefox, Safari, Edge)
- Test **responsive design** on mobile devices
- Test all **user roles** (Admin, Lecturer, Student)

---

## Documentation Standards

- Keep documentation **up-to-date** with code changes
- Use **clear, concise language**
- Include **code examples** where helpful
- Add **screenshots** for UI changes
- Document **all API endpoints**

---

## Getting Help

If you need help:

- Check the existing **documentation**
- Search **closed issues** for similar problems
- Ask in **pull request comments**
- Contact the maintainer directly

---

## Recognition

Contributors will be:

- Listed in the project's **CONTRIBUTORS.md** file
- Credited in **release notes**
- Acknowledged in the **README.md**

---

## License

By contributing to EduTrack, you agree that your contributions will be licensed under the same license as the project.

---

## Contact

**Project Maintainer:** Kundananji Simukonda  
**Email:** kundananjisimukonda@gmail.com  
**Phone:** +260 967 591 264 / +260 971 863 462

---

Thank you for contributing to EduTrack!