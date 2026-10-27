# Boost General Rules

## Testing Practices

### Factory Creation

When creating a new model, create a corresponding factory in `database/factories/`:

```bash
php artisan make:factory ModelNameFactory --model=ModelName
```

Each factory should:
- Define `definition()` method returning relevant default states
- Include at least 2 state methods (e.g., `active()`, `pending()`, `published()`)
- Use `$this->faker` for realistic data
- Be used in seeder or test with `$factory->create()`

### Seeder Usage

Instead of manual insertion, use factories in DatabaseSeeder:

```php
public function run(): void
{
    User::factory(10)->create();
    Participant::factory(5)->create();
    Mentor::factory(3)->create();
    // ... other models
}
```

### Test Implementation

For new features, write corresponding tests:

```bash
php artisan make:test ModelNameTest
```

Each test file should include:
- Test class name ending with `Test`
- Test for main functionality
- Test for edge cases
- Test for authorization where applicable

## Code Style and Conventions

### Model Structure

Each model should:
- Use constructor property promotion where applicable
- Use typed properties with proper casts
- Define relationship methods
- Include helper methods for business logic
- Document public methods with docblocks

### Validation and Security

- Use HTTP response codes appropriately (200, 201, 400, 401, 403, 404, 422)
- Implement CSRF protection for mutating routes
- Rate limit authentication and mutating endpoints
- Use appropriate middleware for role-based access control
- Sanitize file uploads and validate against allowed extensions

### API Structure

- Use Eloquent API Resources for structured responses
- Implement proper error handling with consistent JSON structure
- Include API versioning when applicable
- Document routes with proper route naming conventions
- Use middleware group to protect API routes

## File and Folder Structure

### Consistent Naming

- Use snake_case for file names (`application_form.blade.php` not `ApplicationForm.blade.php`)
- Use kebab-case for route names (`applications.store` not `applications.create`)
- Use TitleCase for class names and constants
- Use lowercase for variables and method names

### Documentation

All code changes should include:
- Docblocks for classes and public methods
- Type hints for method parameters and return types
- Inline comments for complex logic only
- Update of README.md where relevant

## Database Migrations

### Naming Convention

- Use `YYYY_MM_DD_HHMMSS_` prefix
- Include clear description of changes
- Group related changes in single migration

### Best Practices

- Use soft deletes when needed
- Add nullable columns with default values
- Include indexes for frequently queried columns
- Add foreign key constraints where appropriate
- Write reversible migrations when possible

## Frontend Integration

### React Components

- Use PascalCase for component names
- Use kebab-case for custom HTML elements
- Keep components small and focused
- Extract common UI elements into reusable components
- Use React hooks appropriately

### Tailwind CSS

- Use utility classes for styling
- Avoid custom CSS files where possible
- Keep responsive design in mind
- Use consistent spacing scale
- Follow the design system defined in figma/specs

## Deployment and Production

### Environment Variables

All configuration should be moved to environment variables:
- Database credentials
- API keys
- Email settings
- Third-party service credentials

### Security

- Never commit sensitive files to version control
- Use secure session configuration
- Implement proper error handling that doesn't leak information
- Use HTTPS in production
- Set appropriate Content Security Policy headers

## Version Control

### Commit Messages

- Keep commit messages under 72 characters
- Start with a verb (add, fix, refactor, docs)
- Include issue number when applicable
- Keep a co-authored-by line for team collaboration

### Pull Requests

- Use descriptive branch names
- Include clear PR descriptions
- Add screenshots or GIFs for UI changes
- Reference relevant issue numbers
- Request review from appropriate team members

## Performance Considerations

### Database Queries

- Use eager loading for relationships
- Add indexes to frequently queried columns
- Limit queries with pagination
- Use query scopes for common filters
- Consider cache strategies for expensive queries

### Frontend Performance

- Optimize images and use lazy loading
- Minify and bundle JavaScript
- Use HTTP/2 push for critical assets
- Implement proper caching strategies
- Optimize core web vitals

## Error Handling

### Logging

- Log all errors to error tracking service
- Log at appropriate level (DEBUG, INFO, WARN, ERROR)
- Include request context in logs when appropriate
- Regularly review logs for patterns

### Error Responses

- Return error messages in user-friendly format
- Include error codes for client applications
- Don't expose sensitive information in error messages
- Graceful handling of unexpected errors

## Continuous Integration

### Testing Pipeline

- Run unit tests on every commit
- Run feature tests for new functionality
- Run linting and type checking
- Deploy to staging environment for integration testing
- Automated deployment to production after manual approval

### Code Quality

- Enforce code style with linting
- Run type checking
- Static analysis for potential security issues
- Code coverage tracking and improvements
- Regular code reviews of critical changes

## Code Refactoring

### When to Refactor

- Code is difficult to maintain
- Performance is impacted
- Testability is compromised
- Developers are avoiding certain parts of code

### Refactoring Guidelines

- Make small, focused changes
- Maintain existing functionality during refactoring
- Update tests to verify refactored code
- Communicate with team before major refactoring

## Local Development

### Development Setup

1. Run `composer install` to install PHP dependencies
2. Run `npm install` to install Node.js dependencies
3. Copy `.env.example` to `.env` and configure database
4. Run `php artisan key:generate`
5. Run `php artisan migrate` to create database tables
6. Run `php artisan db:seed` to populate with test data
7. Run `composer run dev` to start development server

### Debugging

- Use Laravel Telescope for query and request logging
- Use Laravel Debugger for route and middleware tracing
- Use browser dev tools for frontend debugging
- Enable debug mode only in development
- Use proper error handling for production

## Documentation

### Markdown Files

- Use Markdown with GFM (GitHub Flavored Markdown)
- Keep documentation up to date with code changes
- Include code examples where helpful
- Document API endpoints and their parameters
- Document deployment procedures

### API Documentation

- Use API documentation tools like Swagger/OpenAPI
- Document authentication requirements
- Include request/response examples
- Document error codes and messages
- Include example client usage

### Architecture Documentation

- Document project structure and reasoning
- Document design decisions and trade-offs
- Include diagrams where helpful
- Document data flow between components
- Document deployment architecture

## Quality Assurance

### Testing Strategy

- Test every code change
- Test edge cases and error conditions
- Test security aspects of all changes
- Test performance where relevant
- Test cross-browser compatibility

### Code Review

- Every PR should be reviewed by another developer
- Check for code style compliance
- Verify test coverage
- Ensure no security vulnerabilities
- Confirm adherence to project guidelines

### Performance Testing

- Monitor application performance in staging
- Set up performance monitoring in production
- Regularly review and optimize performance
- Test under load where appropriate
- Monitor database performance

## Compliance and Accessibility

### Accessibility

- Ensure WCAG 2.1 AA compliance
- Use semantic HTML elements
- Provide alternative text for images
- Ensure keyboard navigation
- Test with screen readers

### Data Privacy

- Implement GDPR compliance
- Get proper consent for data collection
- Implement data retention policies
- Secure sensitive data
- Provide data export and deletion

## DevOps

### Deployment Strategy

- Use blue-green deployment for critical changes
- Implement canary releases for new features
- Automate deployment process
- Have rollback procedures ready
- Monitor deployments for issues

### Monitoring

- Set up application monitoring
- Monitor database performance
- Track error rates
- Monitor resource usage
- Set up alerts for critical issues

### Backup and Recovery

- Regular database backups
- Automated backup procedures
- Disaster recovery plans
- Testing of backup and restore procedures

## Development Workflow

### Task Management

- Use GitHub Issues for task tracking
- Break tasks into small, manageable pieces
- Assign tasks to appropriate team members
- Regular standup meetings
- Review progress frequently

### Code Changes

- Create feature branches for each task
- Use pull requests for code review
- Use descriptive commit messages
- Keep changes focused on single issue
- Update tests and documentation

### Team Collaboration

- Regular code reviews
- Pair programming when needed
- Knowledge sharing sessions
- Mentorship for junior developers
- Documentation of best practices

## Conclusion

This document provides a comprehensive guide for working with the Laravel Boost project. By following these guidelines, the development team can ensure consistent, high-quality code that is maintainable, secure, and performs well. Regular review and updates to these guidelines are encouraged to reflect the evolving nature of the project and new best practices in software development.

---

*Note: This is a living document that will be updated as the project evolves and new best practices are discovered.*

## Kepatuhan

### Kebijakan Produksi

- Jangan commit ke kode rahasia, kunci, token, atau kredensial apa pun.
- Gunakan HTTPS di produksi.
- Terapkan Content Security Policy (CSP) yang tepat.
- Implementasikan proper error handling yang tidak membocorkan informasi.
- Gunakan kata sandi yang aman untuk database dan layanan eksternal.

### Perlindungan Data

- Patuhi GDPR/CCPA di mana berlaku.
- Dapatkan persetujuan yang tepat untuk pengumpulan data.
- Implementasikan kebijakan retensi data.
- Amankan transfer data menggunakan TLS.
- Lakukan hashing kata sandi menggunakan algoritma yang aman (bcrypt, argon2).

### Pemantauan Keamanan

- Pantau log untuk akses tidak sah.
- Tetapkan flag untuk login gagal dan rate limiting.
- Lakukan pemindaian kerentanan secara teratur.
- Pertahankan library dan dependensi pada versi terbaru.
- Jalankan peringatan keamanan secara berkala.

## Produktivitas Pengembang

### Alat dan Lingkungan

- Gunakan IDE/editor dengan dukungan PHP yang baik (e.g., PHPStorm, VS Code dengan extension PHP).
- Pertahankan environment pengembangan lokal yang sinkron dengan produksi.
- Gunakan Docker untuk isolasi lingkungan jika diperlukan.
- Pertahankan dependensi terbaru tetapi uji kompatibilitas silang.

### Kepatuhan Linting dan Typing

- Jalankan `vendor/bin/pint --format` sebelum setiap commit untuk memformat kode.
- Jalankan `php artisan test --coverage` untuk memeriksa cakupan.
- Gunakan stilton type-hint checks jika menggunakan TypePHP.

### Alur Kerja Pengujian

- Terapkan red/green engineering: tulis gagal test, buat pass, lalu refactor.
- Jalankan unit tests terlebih dahulu sebelum test integration.
- Gunakan kontainer uji terisolasi untuk database.
- Mock external API calls di unit test.

### Manajemen Pengetahuan

- Dokumentasikan semua keputusan teknis dengan penjelasan singkat.
- Pertahankan dokumentasi README yang terus diperbarui.
- Lakukan review pengetahuan sesekali (brown bag) untuk berbagi pelajaran yang dipetik.

### Penanganan Masalah

- Jalankan `php artisan tinker` untuk debugging cepat, bukan alat verifikasi.
- Gunakan database query tools (e.g., Laravel Telescope, Query Builder Debug) untuk meninjau kueri.
- Untuk error yang tidak bisa diselesaikan, log stack trace lengkap, periksa environment, dan dokumentasikan langkah reproduksi.

### Mengembangkan API yang Ramah

- Return kontrak JSON yang konsisten (e.g., `{success: true, data: ..., errors: null}`).
- Gunakan HTTP status codes yang sesuai (201 untuk created, 204 untuk deleted).
- Dokumentasikan semua endpoint dengan parameter dan contoh response di `routes/api.php` atau file terpisah.

### Diagram Proses

1. Buat branch fitur dari main.
2. Commit perubahan kecil dan sering.
3. Jalankan test lokal.
4. Bikin PR dengan deskripsi lengkap.
5. Code review dari sesama tim.
6. Jalankan CI penuh (test + lint + type-check).
7. Merge setelah lulus semua gate.
8. Deploy ke staging untuk verifikasi.
9. Lakukan review manual + jalankan end-to-end tests.
10. Approval untuk produksi, merge ke main, jalankan pipeline otomatis.

Catatan: Batasi lompatan konteks; setiap interaksi fokus hanya pada satu domainnya (testing, infrastruktur, dll). Gunakan task description yang tepat saat bikin subtask.

## Panduan Akhir

### Keberlanjutan

- Pastikan semua `composer` dan `npm` lockfiles di-commit (tidak autogenerated hanya di CI).
- Pertahankan diagram arsip yang terus diperbarui (`.github/FUNDING.yaml`, `CHANGELOG.md`).
- Jalankan `vendor/bin/pint --format` sebelum commit akhir untuk semua file PHP.

### Pemantauan Tim

- Setiap PR secara otomatis memicu `phpunit`, `pint --test`, `phpstan` (jika ada).
- Notifikasi saluran Slack untuk error CI, coverage menurun, atau deploy gagal.
- Pantau `php artisan tinker` penting digunakan hanya saat debugging akut.
- Pertahankan peta tipe melalui `php artisan test --coverage` dan pantau tren.

### Pengantar Database

- Gunakan migrations untuk perubahan skema.
- Pertahankan saklar strategi database dengan `env(DB_CONNECTION)`.
- Gunakan fitur `NeonMigrationConnection` yang disediakan untuk koneksi Neon dalam konsol.
- Semua model memiliki factories yang sesuai, gunakan `factory(ModelName::class)->create()` di seeder/tes.

### Penanganan Frontline

- Untuk bug frontend, ikuti: `npm run build` → cek `storage/logs/laravel.log` → buka Chrome DevTools → periksa network + console.
- Untuk error auth: cek `config/auth.php`, route `web.php`, middleware `EnsureRole`, status `users.verification_status`, `users.status`.
- Untuk timeout PDF generation: Periksa `brokershot`, `config/browsershot.php`, pastikan Chrome lokal terinstal.

### Transisi Kontrak

- Semua kontrak API: Dokumentasikan di `routes/api.php`, miliki resource API.
- Semua kontrak publik → nama route (`route('api.search')`), bukan hardcoded URL.
- Semua data pengguna yang terproteksi → otorisasi dengan policy, bukan manual pengecekan role.

---

*Ceklist Kualitas Sebelum Merge*

- [ ] Semua perubahan melalui `php artisan test` → PASS
- [ ] `vendor/bin/pint --test --format agent` → PASS  
- [ ] `php artisan test --coverage` ≥ 80%
- [ ] Tidak ada file sementara, `.env` kebocoran
- [ ] Semua linting, type-checking, tests lolos
- [ ] CHANGELOG.md diperbarui