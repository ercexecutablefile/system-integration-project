using System.Net.Http.Json;
using System.Text.Json;
using System.Text.Json.Serialization;

namespace DesktopEventClient;

public sealed class EventApiClient
{
    private const string ApiKey = "student-event-api-key-2026";
    private readonly HttpClient _httpClient;
    private readonly JsonSerializerOptions _jsonOptions = new()
    {
        PropertyNameCaseInsensitive = true
    };

    public EventApiClient()
    {
        _httpClient = new HttpClient
        {
            BaseAddress = new Uri("http://localhost/Project/Event-API/index.php/")
        };
        _httpClient.DefaultRequestHeaders.Add("X-API-Key", ApiKey);
    }

    public async Task<List<EventRecord>> GetEventsAsync()
    {
        ApiResponse<List<EventRecord>>? response = await _httpClient.GetFromJsonAsync<ApiResponse<List<EventRecord>>>("events", _jsonOptions);
        return response?.Data ?? [];
    }

    public async Task<List<AttendeeRecord>> GetAttendeesAsync(int eventId)
    {
        ApiResponse<List<AttendeeRecord>>? response = await _httpClient.GetFromJsonAsync<ApiResponse<List<AttendeeRecord>>>($"attendees?event_id={eventId}", _jsonOptions);
        return response?.Data ?? [];
    }

    public async Task SaveEventAsync(EventRecord record)
    {
        EventInput payload = new(record.EventName, record.EventDesc, record.EventDate, record.MaxCapacity, 1);
        HttpResponseMessage response = record.Id > 0
            ? await _httpClient.PutAsJsonAsync($"events/{record.Id}", payload)
            : await _httpClient.PostAsJsonAsync("events", payload);

        await EnsureSuccessAsync(response);
    }

    public async Task DeleteEventAsync(int eventId)
    {
        HttpResponseMessage response = await _httpClient.DeleteAsync($"events/{eventId}");
        await EnsureSuccessAsync(response);
    }

    public async Task AddAttendeeAsync(string name, string email, int eventId)
    {
        HttpResponseMessage response = await _httpClient.PostAsJsonAsync("attendees", new AttendeeInput(name, email, eventId));
        await EnsureSuccessAsync(response);
    }

    public async Task DeleteAttendeeAsync(int attendeeId)
    {
        HttpResponseMessage response = await _httpClient.DeleteAsync($"attendees/{attendeeId}");
        await EnsureSuccessAsync(response);
    }

    private async Task EnsureSuccessAsync(HttpResponseMessage response)
    {
        if (response.IsSuccessStatusCode) {
            return;
        }

        string content = await response.Content.ReadAsStringAsync();
        ApiResponse<object>? apiResponse = JsonSerializer.Deserialize<ApiResponse<object>>(content, _jsonOptions);
        throw new InvalidOperationException(apiResponse?.Message ?? content);
    }
}

public sealed record ApiResponse<T>(
    [property: JsonPropertyName("status")] string Status,
    [property: JsonPropertyName("message")] string? Message,
    [property: JsonPropertyName("data")] T? Data);

public sealed class EventRecord
{
    [JsonPropertyName("id")]
    public int Id { get; set; }

    [JsonPropertyName("event_name")]
    public string EventName { get; set; } = "";

    [JsonPropertyName("event_desc")]
    public string EventDesc { get; set; } = "";

    [JsonPropertyName("event_date")]
    public string EventDate { get; set; } = DateTime.Today.ToString("yyyy-MM-dd");

    [JsonPropertyName("max_capacity")]
    public int MaxCapacity { get; set; }

    [JsonPropertyName("registered_guests")]
    public int RegisteredGuests { get; set; }
}

public sealed class AttendeeRecord
{
    [JsonPropertyName("id")]
    public int Id { get; set; }

    [JsonPropertyName("name")]
    public string Name { get; set; } = "";

    [JsonPropertyName("email")]
    public string Email { get; set; } = "";

    [JsonPropertyName("event_name")]
    public string EventName { get; set; } = "";
}

public sealed record EventInput(
    [property: JsonPropertyName("event_name")] string EventName,
    [property: JsonPropertyName("event_desc")] string EventDesc,
    [property: JsonPropertyName("event_date")] string EventDate,
    [property: JsonPropertyName("max_capacity")] int MaxCapacity,
    [property: JsonPropertyName("created_by")] int CreatedBy);

public sealed record AttendeeInput(
    [property: JsonPropertyName("name")] string Name,
    [property: JsonPropertyName("email")] string Email,
    [property: JsonPropertyName("event_id")] int EventId);
