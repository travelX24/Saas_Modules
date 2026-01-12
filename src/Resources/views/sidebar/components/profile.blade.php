@props([
  'name' => 'User',
  'role' => @tr('System Admin'),
  'photo' => null,
])

<div class="profile-section">
    <div class="profile-img">
        @if($photo)
            <img src="{{ $photo }}" alt="avatar" style="width:100%;height:100%;object-fit:cover;">
        @else
            <i class="fas fa-user"></i>
        @endif
    </div>

    <div class="profile-info">
        <span class="profile-name">{{ $name }}</span>
        <span class="profile-role">{{ $role }}</span>
    </div>
</div>
