<div class="table-responsive">
    <table class="table report-table align-middle">
        <thead>
            <tr>
                <th>Incident</th>
                <th>Type</th>
                @if(!$compact)<th>Priority</th>@endif
                <th>Status</th>
                <th>Location</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $incident)
            <tr>
                <td>{{ $incident->incident_number }}</td>
                <td>{{ $incident->incident_type }}</td>
                @if(!$compact)<td><span class="status-label">{{ $incident->priority ?: 'Not set' }}</span></td>@endif
                <td><span class="status-label">{{ ucfirst(str_replace('_', ' ', $incident->status)) }}</span></td>
                <td>{{ $incident->formattedAddress() }}</td>
                <td>{{ $incident->created_at?->format('M d, Y H:i') ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $compact ? 5 : 6 }}" class="empty-row">No incidents found for the selected filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>