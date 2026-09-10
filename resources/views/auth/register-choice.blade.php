@include('auth.login-choice', [
    'initialTab' => 'register',
    'departments' => $departments ?? \App\Models\Department::where('status', 'active')->orderBy('name')->get(),
])
