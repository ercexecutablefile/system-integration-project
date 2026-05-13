using System.Drawing.Text;

namespace DesktopEventClient;

internal static class AppTheme
{
    public static readonly Color Primary = Color.FromArgb(23, 78, 255);
    public static readonly Color SoftPrimary = Color.FromArgb(126, 164, 255);
    public static readonly Color Text = Color.FromArgb(18, 24, 32);
    public static readonly Color Muted = Color.FromArgb(92, 100, 112);
    public static readonly Color Surface = Color.FromArgb(246, 247, 249);

    private static readonly PrivateFontCollection Fonts = new();
    private static bool _loaded;

    public static Font Regular(float size, FontStyle style = FontStyle.Regular) =>
        new(FontFamily(1), size, style);

    public static Font Bold(float size, FontStyle style = FontStyle.Bold) =>
        new(FontFamily(0), size, style);

    public static string AssetPath(string fileName) =>
        Path.Combine(AppContext.BaseDirectory, "Assets", fileName);

    private static FontFamily FontFamily(int preferredIndex)
    {
        LoadFonts();

        if (Fonts.Families.Length > preferredIndex) {
            return Fonts.Families[preferredIndex];
        }

        if (Fonts.Families.Length > 0) {
            return Fonts.Families[0];
        }

        return System.Drawing.FontFamily.GenericSansSerif;
    }

    private static void LoadFonts()
    {
        if (_loaded) {
            return;
        }

        string fontDir = Path.Combine(AppContext.BaseDirectory, "Fonts");
        foreach (string file in new[] { "SFPRODISPLAYBOLD.OTF", "SFPRODISPLAYREGULAR.OTF" }) {
            string path = Path.Combine(fontDir, file);
            if (File.Exists(path)) {
                Fonts.AddFontFile(path);
            }
        }

        _loaded = true;
    }
}
