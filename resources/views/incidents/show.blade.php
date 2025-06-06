@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge {{ $incident->status_badge_class }}">
                                    {{ ucfirst(str_replace('_', ' ', $incident->status)) }}
                                </span>
                                <span class="badge {{ $incident->priority_badge_class }}">
                                    {{ ucfirst($incident->priority) }} Priority
                                </span>
                            </div>
                            <h1 class="text-eco-primary mb-2">{{ $incident->title }}</h1>
                            <div class="d-flex align-items-center text-muted">
                                <i class="{{ $incident->category->icon ?? 'bi-tag' }} me-2"
                                   style="color: {{ $incident->category->color }}"></i>
                                <span class="me-3">{{ $incident->category->name }}</span>
                                <i class="bi bi-calendar me-1"></i>
                                <span class="me-3">{{ $incident->created_at->format('M j, Y') }}</span>
                                <i class="bi bi-person me-1"></i>
                                <span>{{ $incident->display_name }}</span>
                            </div>
                        </div>
                        @auth
                            @if(Auth::id() === $incident->user_id || Auth::user()->role === 'admin')
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('incidents.edit', $incident) }}">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('incidents.destroy', $incident) }}" method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this incident?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Photos -->
            @if($incident->photos->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-camera me-2"></i>Photos ({{ $incident->photos->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($incident->photos as $photo)
                                <div class="col-md-6 mb-3">
                                    <div class="position-relative">
                                        <img src="{{ Storage::url($photo->path) }}"
                                             class="img-fluid rounded shadow-sm"
                                             alt="{{ $photo->original_name }}"
                                             style="width: 100%; height: 250px; object-fit: cover; cursor: pointer;"
                                             data-bs-toggle="modal"
                                             data-bs-target="#photoModal{{ $photo->id }}">
                                        @if($photo->caption)
                                            <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white p-2 rounded-bottom">
                                                <small>{{ $photo->caption }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Photo Modal -->
                                <div class="modal fade" id="photoModal{{ $photo->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ $photo->original_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ Storage::url($photo->path) }}"
                                                     class="img-fluid" alt="{{ $photo->original_name }}">
                                                @if($photo->caption)
                                                    <p class="mt-3 text-muted">{{ $photo->caption }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Description -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-file-text me-2"></i>Description
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-line;">{{ $incident->description }}</p>
                </div>
            </div>

            <!-- Admin Notes -->
            @if($incident->admin_notes && (Auth::check() && (Auth::user()->role === 'admin' || Auth::id() === $incident->user_id)))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-shield-check me-2"></i>Administrative Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0" style="white-space: pre-line;">{{ $incident->admin_notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-2"></i>Back to All Reports
                        </a>
                        @auth
                            <a href="{{ route('incidents.create') }}" class="btn btn-eco-primary btn-sm">
                                <i class="bi bi-plus-circle me-2"></i>Report New Issue
                            </a>
                        @endauth
                        @if($incident->latitude && $incident->longitude)
                            <a href="{{ route('incidents.map') }}?lat={{ $incident->latitude }}&lng={{ $incident->longitude }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-geo-alt me-2"></i>View on Map
                            </a>
                        @endif

                        @auth
                            <button type="button" class="btn btn-outline-eco-primary btn-sm" onclick="openEvidenceModal()">
                                <i class="bi bi-people me-2"></i>Add Evidence
                            </button>

                            <button type="button" class="btn btn-outline-info btn-sm" onclick="viewCommunityEvidence()">
                                <i class="bi bi-eye me-2"></i>View Evidence
                                @if($incident->evidence_count > 0)
                                    <span class="badge bg-success ms-1">{{ $incident->evidence_count }}</span>
                                @endif
                            </button>
                        @endauth
                    </div>
                </div>
            </div>



            <!-- Incident Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Incident Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge {{ $incident->status_badge_class }}">
                                {{ ucfirst(str_replace('_', ' ', $incident->status)) }}
                            </span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Priority</small>
                            <span class="badge {{ $incident->priority_badge_class }}">
                                {{ ucfirst($incident->priority) }}
                            </span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Category</small>
                            <div class="d-flex align-items-center">
                                <i class="{{ $incident->category->icon ?? 'bi-tag' }} me-1"
                                   style="color: {{ $incident->category->color }}"></i>
                                <small>{{ $incident->category->name }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Reported</small>
                            <small>{{ $incident->created_at->format('M j, Y') }}</small>
                        </div>
                        @if($incident->resolved_at)
                            <div class="col-6">
                                <small class="text-muted d-block">Resolved</small>
                                <small>{{ $incident->resolved_at->format('M j, Y') }}</small>
                            </div>
                            @if($incident->resolvedBy)
                                <div class="col-6">
                                    <small class="text-muted d-block">Resolved By</small>
                                    <small>{{ $incident->resolvedBy->name }}</small>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Reporter Info -->
            @if(!$incident->is_anonymous)
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-person me-2"></i>Reported By
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-eco-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 40px; height: 40px;">
                                <i class="bi bi-person-fill text-white"></i>
                            </div>
                            <div>
                                <div class="fw-bold">{{ $incident->user->name ?? 'Unknown User' }}</div>
                                <small class="text-muted">Community Member</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.btn-outline-eco-primary {
    color: var(--eco-primary);
    border-color: var(--eco-primary);
}

.btn-outline-eco-primary:hover {
    background-color: var(--eco-primary);
    border-color: var(--eco-primary);
    color: white;
}
</style>

<script>
const incidentId = {{ $incident->id }};

function openEvidenceModal() {
    Swal.fire({
        title: 'Add Supporting Evidence',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label">Evidence Type</label>
                    <select class="form-select" id="evidenceType" onchange="toggleEvidenceFields()">
                        <option value="comment">Comment</option>
                        <option value="photo">Photo</option>
                    </select>
                </div>

                <div id="commentField" class="mb-3">
                    <label class="form-label">Your Comment</label>
                    <textarea class="form-control" id="evidenceComment" rows="4"
                              placeholder="Share additional information about this incident..."></textarea>
                </div>

                <div id="photoField" class="mb-3" style="display: none;">
                    <label class="form-label">Upload Photo</label>
                    <input type="file" class="form-control" id="evidencePhoto" accept="image/*">
                    <div class="form-text">Max file size: 5MB. Supported formats: JPG, PNG, GIF</div>

                    <div class="mt-2">
                        <label class="form-label">Photo Description (Optional)</label>
                        <input type="text" class="form-control" id="photoDescription"
                               placeholder="Describe what this photo shows...">
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Evidence',
        cancelButtonText: 'Cancel',
        width: '600px',
        preConfirm: () => {
            const type = document.getElementById('evidenceType').value;
            const comment = document.getElementById('evidenceComment').value;
            const photo = document.getElementById('evidencePhoto').files[0];
            const photoDescription = document.getElementById('photoDescription').value;

            if (type === 'comment' && !comment.trim()) {
                Swal.showValidationMessage('Please enter a comment');
                return false;
            }

            if (type === 'photo' && !photo) {
                Swal.showValidationMessage('Please select a photo');
                return false;
            }

            return { type, comment, photo, photoDescription };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitEvidence(result.value);
        }
    });
}

function toggleEvidenceFields() {
    const type = document.getElementById('evidenceType').value;
    const commentField = document.getElementById('commentField');
    const photoField = document.getElementById('photoField');

    if (type === 'comment') {
        commentField.style.display = 'block';
        photoField.style.display = 'none';
    } else {
        commentField.style.display = 'none';
        photoField.style.display = 'block';
    }
}

function submitEvidence(data) {
    const formData = new FormData();
    formData.append('type', data.type);

    if (data.type === 'comment') {
        formData.append('content', data.comment);
    } else {
        formData.append('photo', data.photo);
        formData.append('photo_description', data.photoDescription);
    }

    // Show loading
    Swal.fire({
        title: 'Submitting Evidence...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/incidents/${incidentId}/evidence`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Evidence Submitted!',
                text: data.message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            throw new Error(data.message || 'Failed to submit evidence');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to submit evidence. Please try again.'
        });
    });
}

function viewCommunityEvidence() {
    // Show loading
    Swal.fire({
        title: 'Loading Evidence...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/incidents/${incidentId}/evidence`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayEvidenceModal(data.evidence);
        } else {
            throw new Error('Failed to load evidence');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load evidence. Please try again.'
        });
    });
}

function displayEvidenceModal(evidence) {
    let evidenceHtml = '';

    if (evidence.length === 0) {
        evidenceHtml = '<p class="text-muted text-center">No community evidence has been submitted yet.</p>';
    } else {
        evidenceHtml = evidence.map(item => {
            if (item.type === 'comment') {
                return `
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <strong class="text-eco-primary">${item.user_name}</strong>
                            <small class="text-muted">${item.created_at}</small>
                        </div>
                        <p class="mb-0">${item.content}</p>
                        ${item.is_verified ? '<span class="badge bg-success mt-2">Verified</span>' : ''}
                    </div>
                `;
            } else {
                return `
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <strong class="text-eco-primary">${item.user_name}</strong>
                            <small class="text-muted">${item.created_at}</small>
                        </div>
                        <div class="mb-2">
                            <img src="${item.file_url}" class="img-fluid rounded" style="max-height: 200px;" alt="Evidence photo">
                        </div>
                        ${item.content ? `<p class="mb-1">${item.content}</p>` : ''}
                        <small class="text-muted">${item.file_name} (${item.formatted_file_size})</small>
                        ${item.is_verified ? '<br><span class="badge bg-success mt-2">Verified</span>' : ''}
                    </div>
                `;
            }
        }).join('');
    }

    Swal.fire({
        title: 'Community Evidence',
        html: `
            <div class="text-start" style="max-height: 400px; overflow-y: auto;">
                ${evidenceHtml}
            </div>
        `,
        width: '700px',
        confirmButtonText: 'Close'
    });
}
</script>
@endsection
