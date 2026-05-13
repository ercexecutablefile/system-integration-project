# Student Event Desktop Client

This is the desktop system for the integration project. It does not connect directly to MySQL.

Flow:

```text
DesktopEventClient -> Event-API -> event_management MySQL database
```

Run it with:

```powershell
dotnet run --project C:\xampp\htdocs\Project\DesktopEventClient
```

Before running, start Apache and MySQL in XAMPP.

## Login

The desktop client opens with an SMS-inspired login UI using the original project fonts and assets.

Sample account:

- Username: `kaye`
- Email address: `kayeexample@gmail.com`
- Password: `kaye1S23`

The cleaned student email `kaye.dela.cruz@student.edu` also works with the same username and password.
