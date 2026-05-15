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
