namespace DesktopEventClient;

public sealed class MainForm : Form
{
    private readonly EventApiClient _api = new();
    private readonly BindingSource _eventsSource = new();
    private readonly BindingSource _attendeesSource = new();

    private readonly DataGridView _eventsGrid = new();
    private readonly DataGridView _attendeesGrid = new();
    private readonly TextBox _eventName = new();
    private readonly TextBox _eventDescription = new();
    private readonly DateTimePicker _eventDate = new();
    private readonly NumericUpDown _maxCapacity = new();
    private readonly TextBox _attendeeName = new();
    private readonly TextBox _attendeeEmail = new();

    public MainForm()
    {
        Text = "Student Event Desktop Client";
        Width = 1120;
        Height = 720;
        StartPosition = FormStartPosition.CenterScreen;

        BuildLayout();
        Load += async (_, _) => await RefreshEventsAsync();
    }

    private void BuildLayout()
    {
        TableLayoutPanel root = new()
        {
            Dock = DockStyle.Fill,
            ColumnCount = 2,
            RowCount = 1,
            Padding = new Padding(12)
        };
        root.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 62));
        root.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 38));

        _eventsGrid.Dock = DockStyle.Fill;
        _eventsGrid.ReadOnly = true;
        _eventsGrid.AutoGenerateColumns = true;
        _eventsGrid.SelectionMode = DataGridViewSelectionMode.FullRowSelect;
        _eventsGrid.MultiSelect = false;
        _eventsGrid.DataSource = _eventsSource;
        _eventsGrid.SelectionChanged += async (_, _) =>
        {
            FillEventForm();
            await RefreshAttendeesAsync();
        };

        FlowLayoutPanel eventButtons = new() { Dock = DockStyle.Fill, FlowDirection = FlowDirection.LeftToRight };
        eventButtons.Controls.Add(Button("Refresh", async (_, _) => await RefreshEventsAsync()));
        eventButtons.Controls.Add(Button("New", (_, _) => ClearEventForm()));
        eventButtons.Controls.Add(Button("Save Event", async (_, _) => await SaveEventAsync()));
        eventButtons.Controls.Add(Button("Delete Event", async (_, _) => await DeleteEventAsync()));

        TableLayoutPanel eventForm = new() { Dock = DockStyle.Fill, ColumnCount = 2, RowCount = 5 };
        eventForm.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 110));
        eventForm.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100));
        AddRow(eventForm, 0, "Name", _eventName);
        AddRow(eventForm, 1, "Description", _eventDescription);
        AddRow(eventForm, 2, "Date", _eventDate);
        _maxCapacity.Minimum = 1;
        _maxCapacity.Maximum = 10000;
        AddRow(eventForm, 3, "Capacity", _maxCapacity);
        eventForm.Controls.Add(eventButtons, 1, 4);

        TableLayoutPanel left = new() { Dock = DockStyle.Fill, RowCount = 3 };
        left.RowStyles.Add(new RowStyle(SizeType.Absolute, 165));
        left.RowStyles.Add(new RowStyle(SizeType.Absolute, 34));
        left.RowStyles.Add(new RowStyle(SizeType.Percent, 100));
        left.Controls.Add(Label("Event CRUD"), 0, 0);
        left.Controls.Add(eventForm, 0, 0);
        left.Controls.Add(Label("Events from Shared MySQL Database via API"), 0, 1);
        left.Controls.Add(_eventsGrid, 0, 2);

        _attendeesGrid.Dock = DockStyle.Fill;
        _attendeesGrid.ReadOnly = true;
        _attendeesGrid.AutoGenerateColumns = true;
        _attendeesGrid.SelectionMode = DataGridViewSelectionMode.FullRowSelect;
        _attendeesGrid.MultiSelect = false;
        _attendeesGrid.DataSource = _attendeesSource;

        FlowLayoutPanel attendeeButtons = new() { Dock = DockStyle.Fill, FlowDirection = FlowDirection.LeftToRight };
        attendeeButtons.Controls.Add(Button("Add Attendee", async (_, _) => await AddAttendeeAsync()));
        attendeeButtons.Controls.Add(Button("Delete Attendee", async (_, _) => await DeleteAttendeeAsync()));

        TableLayoutPanel attendeeForm = new() { Dock = DockStyle.Fill, ColumnCount = 2, RowCount = 3 };
        attendeeForm.ColumnStyles.Add(new ColumnStyle(SizeType.Absolute, 80));
        attendeeForm.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 100));
        AddRow(attendeeForm, 0, "Name", _attendeeName);
        AddRow(attendeeForm, 1, "Email", _attendeeEmail);
        attendeeForm.Controls.Add(attendeeButtons, 1, 2);

        TableLayoutPanel right = new() { Dock = DockStyle.Fill, RowCount = 3 };
        right.RowStyles.Add(new RowStyle(SizeType.Absolute, 122));
        right.RowStyles.Add(new RowStyle(SizeType.Absolute, 34));
        right.RowStyles.Add(new RowStyle(SizeType.Percent, 100));
        right.Controls.Add(attendeeForm, 0, 0);
        right.Controls.Add(Label("Attendees for Selected Event"), 0, 1);
        right.Controls.Add(_attendeesGrid, 0, 2);

        root.Controls.Add(left, 0, 0);
        root.Controls.Add(right, 1, 0);
        Controls.Add(root);
    }

    private static Button Button(string text, EventHandler onClick)
    {
        Button button = new() { Text = text, AutoSize = true, Height = 30 };
        button.Click += onClick;
        return button;
    }

    private static Label Label(string text) => new()
    {
        Text = text,
        Dock = DockStyle.Fill,
        Font = new Font("Segoe UI", 10, FontStyle.Bold),
        TextAlign = ContentAlignment.MiddleLeft
    };

    private static void AddRow(TableLayoutPanel panel, int row, string label, Control control)
    {
        control.Dock = DockStyle.Fill;
        panel.RowStyles.Add(new RowStyle(SizeType.Absolute, 32));
        panel.Controls.Add(new Label { Text = label, Dock = DockStyle.Fill, TextAlign = ContentAlignment.MiddleLeft }, 0, row);
        panel.Controls.Add(control, 1, row);
    }

    private EventRecord? SelectedEvent => _eventsSource.Current as EventRecord;
    private AttendeeRecord? SelectedAttendee => _attendeesSource.Current as AttendeeRecord;

    private async Task RefreshEventsAsync()
    {
        try
        {
            _eventsSource.DataSource = await _api.GetEventsAsync();
            FillEventForm();
            await RefreshAttendeesAsync();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "API Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
        }
    }

    private async Task RefreshAttendeesAsync()
    {
        if (SelectedEvent is null)
        {
            _attendeesSource.DataSource = new List<AttendeeRecord>();
            return;
        }

        _attendeesSource.DataSource = await _api.GetAttendeesAsync(SelectedEvent.Id);
    }

    private void FillEventForm()
    {
        if (SelectedEvent is null)
        {
            ClearEventForm();
            return;
        }

        _eventName.Text = SelectedEvent.EventName;
        _eventDescription.Text = SelectedEvent.EventDesc;
        _eventDate.Value = DateTime.TryParse(SelectedEvent.EventDate, out DateTime parsed) ? parsed : DateTime.Today;
        _maxCapacity.Value = Math.Max(1, SelectedEvent.MaxCapacity);
    }

    private void ClearEventForm()
    {
        _eventsGrid.ClearSelection();
        _eventName.Clear();
        _eventDescription.Clear();
        _eventDate.Value = DateTime.Today;
        _maxCapacity.Value = 1;
    }

    private async Task SaveEventAsync()
    {
        try
        {
            EventRecord record = new()
            {
                Id = SelectedEvent?.Id ?? 0,
                EventName = _eventName.Text,
                EventDesc = _eventDescription.Text,
                EventDate = _eventDate.Value.ToString("yyyy-MM-dd"),
                MaxCapacity = (int)_maxCapacity.Value
            };

            await _api.SaveEventAsync(record);
            await RefreshEventsAsync();
        }
        catch (Exception ex)
        {
            MessageBox.Show(ex.Message, "Save Event", MessageBoxButtons.OK, MessageBoxIcon.Error);
        }
    }

    private async Task DeleteEventAsync()
    {
        if (SelectedEvent is null) {
            return;
        }

        if (MessageBox.Show("Delete selected event?", "Confirm Delete", MessageBoxButtons.YesNo, MessageBoxIcon.Question) != DialogResult.Yes) {
            return;
        }

        await _api.DeleteEventAsync(SelectedEvent.Id);
        await RefreshEventsAsync();
    }

    private async Task AddAttendeeAsync()
    {
        if (SelectedEvent is null) {
            MessageBox.Show("Select an event first.");
            return;
        }

        await _api.AddAttendeeAsync(_attendeeName.Text, _attendeeEmail.Text, SelectedEvent.Id);
        _attendeeName.Clear();
        _attendeeEmail.Clear();
        await RefreshAttendeesAsync();
        await RefreshEventsAsync();
    }

    private async Task DeleteAttendeeAsync()
    {
        if (SelectedAttendee is null) {
            return;
        }

        await _api.DeleteAttendeeAsync(SelectedAttendee.Id);
        await RefreshAttendeesAsync();
        await RefreshEventsAsync();
    }
}
