namespace DesktopEventClient;

public sealed class LoginForm : Form
{
    private const string SampleUsername = "kaye";
    private const string SampleEmail = "kayeexample@gmail.com";
    private const string UpdatedStudentEmail = "kaye.dela.cruz@student.edu";
    private const string SamplePassword = "kaye1S23";

    private readonly TextBox _username = new();
    private readonly TextBox _email = new();
    private readonly TextBox _password = new();
    private readonly Label _error = new();
    private readonly Button _togglePassword = new();

    public LoginForm()
    {
        Text = "Student Event Login";
        StartPosition = FormStartPosition.CenterScreen;
        ClientSize = new Size(860, 540);
        MinimumSize = new Size(860, 540);
        BackColor = AppTheme.Surface;
        FormBorderStyle = FormBorderStyle.FixedSingle;
        MaximizeBox = false;

        BuildLayout();
    }

    private void BuildLayout()
    {
        TableLayoutPanel root = new()
        {
            Dock = DockStyle.Fill,
            ColumnCount = 2,
            Padding = new Padding(0),
        };
        root.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 46));
        root.ColumnStyles.Add(new ColumnStyle(SizeType.Percent, 54));

        Panel artPanel = new()
        {
            Dock = DockStyle.Fill,
            BackColor = Color.FromArgb(241, 243, 247),
            Padding = new Padding(28),
        };

        PictureBox art = new()
        {
            Dock = DockStyle.Fill,
            SizeMode = PictureBoxSizeMode.Zoom,
            Image = LoadImage("loginPic.png"),
        };
        artPanel.Controls.Add(art);

        Panel formPanel = new()
        {
            Dock = DockStyle.Fill,
            Padding = new Padding(48, 54, 54, 46),
            BackColor = AppTheme.Surface,
        };

        GroupBox loginBox = new()
        {
            Dock = DockStyle.Fill,
            Text = "Login",
            Font = AppTheme.Bold(20),
            ForeColor = AppTheme.Text,
            Padding = new Padding(38, 42, 38, 34),
        };

        TableLayoutPanel fields = new()
        {
            Dock = DockStyle.Fill,
            ColumnCount = 1,
            RowCount = 11,
        };
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 28));
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 44));
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 16));
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 28));
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 44));
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 16));
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 28));
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 44));
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 24));
        fields.RowStyles.Add(new RowStyle(SizeType.Absolute, 52));
        fields.RowStyles.Add(new RowStyle(SizeType.Percent, 100));

        fields.Controls.Add(Label("Username"), 0, 0);
        fields.Controls.Add(InputRow(_username, true), 0, 1);
        fields.Controls.Add(Label("Email address"), 0, 3);
        fields.Controls.Add(InputRow(_email, true), 0, 4);
        fields.Controls.Add(Label("Password"), 0, 6);
        fields.Controls.Add(PasswordRow(), 0, 7);

        _error.Dock = DockStyle.Fill;
        _error.Font = AppTheme.Bold(10);
        _error.ForeColor = Color.FromArgb(210, 32, 32);
        _error.TextAlign = ContentAlignment.MiddleLeft;
        fields.Controls.Add(_error, 0, 8);

        FlowLayoutPanel buttons = new()
        {
            Dock = DockStyle.Fill,
            FlowDirection = FlowDirection.LeftToRight,
            WrapContents = false,
        };

        Button login = PrimaryButton("Login");
        login.Click += (_, _) => TryLogin();
        Button exit = SecondaryButton("Exit");
        exit.Click += (_, _) => Close();
        buttons.Controls.Add(login);
        buttons.Controls.Add(exit);
        fields.Controls.Add(buttons, 0, 9);

        Label helper = new()
        {
            Text = "Sample: kaye / kayeexample@gmail.com / kaye1S23",
            Font = AppTheme.Regular(10),
            ForeColor = AppTheme.Muted,
            Dock = DockStyle.Top,
            AutoSize = false,
            Height = 32,
        };
        fields.Controls.Add(helper, 0, 10);

        loginBox.Controls.Add(fields);
        formPanel.Controls.Add(loginBox);

        root.Controls.Add(artPanel, 0, 0);
        root.Controls.Add(formPanel, 1, 0);
        Controls.Add(root);

        _username.Text = SampleUsername;
        _email.Text = SampleEmail;
        _password.Text = SamplePassword;
        AcceptButton = login;
    }

    private static Label Label(string text) => new()
    {
        Text = text,
        Font = AppTheme.Bold(11),
        ForeColor = AppTheme.Text,
        Dock = DockStyle.Fill,
        TextAlign = ContentAlignment.BottomLeft,
    };

    private static Panel InputRow(TextBox textBox, bool includeClear)
    {
        Panel panel = BorderPanel();
        textBox.BorderStyle = BorderStyle.None;
        textBox.Font = AppTheme.Bold(12);
        textBox.Dock = DockStyle.Fill;
        textBox.Margin = new Padding(10, 11, 0, 0);

        panel.Controls.Add(textBox);

        if (includeClear) {
            Button clear = IconButton("clear.png");
            clear.Click += (_, _) => textBox.Clear();
            panel.Controls.Add(clear);
        }

        return panel;
    }

    private Panel PasswordRow()
    {
        Panel panel = BorderPanel();
        _password.BorderStyle = BorderStyle.None;
        _password.Font = AppTheme.Bold(12);
        _password.Dock = DockStyle.Fill;
        _password.Margin = new Padding(10, 11, 0, 0);
        _password.UseSystemPasswordChar = true;

        _togglePassword.Width = 34;
        _togglePassword.Dock = DockStyle.Right;
        _togglePassword.FlatStyle = FlatStyle.Flat;
        _togglePassword.FlatAppearance.BorderSize = 0;
        _togglePassword.Image = LoadImage("eyeClose.png");
        _togglePassword.ImageAlign = ContentAlignment.MiddleCenter;
        _togglePassword.Click += (_, _) =>
        {
            _password.UseSystemPasswordChar = !_password.UseSystemPasswordChar;
            _togglePassword.Image = LoadImage(_password.UseSystemPasswordChar ? "eyeClose.png" : "eyeOpen.png");
        };

        Button clear = IconButton("clear.png");
        clear.Click += (_, _) => _password.Clear();

        panel.Controls.Add(_password);
        panel.Controls.Add(_togglePassword);
        panel.Controls.Add(clear);
        return panel;
    }

    private static Panel BorderPanel() => new()
    {
        Dock = DockStyle.Fill,
        BackColor = Color.White,
        BorderStyle = BorderStyle.FixedSingle,
        Padding = new Padding(0),
    };

    private static Button IconButton(string asset)
    {
        return new Button
        {
            Width = 34,
            Dock = DockStyle.Right,
            FlatStyle = FlatStyle.Flat,
            Image = LoadImage(asset),
            ImageAlign = ContentAlignment.MiddleCenter,
            Cursor = Cursors.Hand,
            TabStop = false,
        }.WithNoBorder();
    }

    private static Button PrimaryButton(string text) => new Button()
    {
        Text = text,
        Width = 154,
        Height = 42,
        BackColor = AppTheme.Primary,
        ForeColor = Color.White,
        FlatStyle = FlatStyle.Flat,
        Font = AppTheme.Bold(11),
        Cursor = Cursors.Hand,
        Margin = new Padding(0, 5, 14, 0),
    }.WithNoBorder();

    private static Button SecondaryButton(string text) => new()
    {
        Text = text,
        Width = 154,
        Height = 42,
        BackColor = AppTheme.Surface,
        ForeColor = AppTheme.Text,
        FlatStyle = FlatStyle.Flat,
        Font = AppTheme.Bold(11),
        Cursor = Cursors.Hand,
        Margin = new Padding(0, 5, 0, 0),
    };

    private void TryLogin()
    {
        bool usernameMatches = string.Equals(_username.Text.Trim(), SampleUsername, StringComparison.OrdinalIgnoreCase);
        bool emailMatches = string.Equals(_email.Text.Trim(), SampleEmail, StringComparison.OrdinalIgnoreCase)
            || string.Equals(_email.Text.Trim(), UpdatedStudentEmail, StringComparison.OrdinalIgnoreCase);
        bool passwordMatches = _password.Text == SamplePassword;

        if (usernameMatches && emailMatches && passwordMatches) {
            Hide();
            MainForm main = new();
            main.FormClosed += (_, _) => Close();
            main.Show();
            return;
        }

        _error.Text = "Invalid login details. Please use the sample account.";
    }

    private static Image? LoadImage(string fileName)
    {
        string path = AppTheme.AssetPath(fileName);
        return File.Exists(path) ? Image.FromFile(path) : null;
    }
}

internal static class ButtonExtensions
{
    public static Button WithNoBorder(this Button button)
    {
        button.FlatAppearance.BorderSize = 0;
        return button;
    }
}
