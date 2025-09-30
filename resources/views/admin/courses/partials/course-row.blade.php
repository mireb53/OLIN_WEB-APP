@php($c = $course)
<tr class="hover:bg-gray-50 transition-colors duration-150" data-course-id="{{ $c->id }}" data-program-id="{{ $c->program_id ?? '' }}" data-status="{{ $c->status }}">
  <td class="px-6 py-5">
    <div class="flex items-start">
      <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 flex-shrink-0 mt-1">
        <svg class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253z"></path>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-sm font-semibold text-slate-800 leading-5 line-clamp-2 break-words">{{ $c->title }}</div>
        <div class="text-xs text-slate-500 mt-1 truncate">{{ $c->course_code ?? 'No code' }}</div>
      </div>
    </div>
  </td>
  <td class="px-6 py-5">
    <div class="flex items-center">
      <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center mr-3">
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
      </div>
      <div>
        <div class="text-sm font-medium text-slate-700">{{ optional($c->instructor)->name ?? 'N/A' }}</div>
        <div class="text-xs text-slate-500">{{ $c->department ?? 'No Department' }}</div>
      </div>
    </div>
  </td>
  <td class="px-6 py-5">
    <div class="flex items-center">
      <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
      </div>
      <span class="text-sm font-semibold text-slate-700">{{ $c->students_count ?? 0 }}</span>
    </div>
  </td>
  <td class="px-6 py-5">
    @if($c->status === 'published')
      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200"><div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>Published</span>
    @elseif($c->status === 'draft')
      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 border border-yellow-200"><div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>Draft</span>
    @else
      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200"><div class="w-2 h-2 bg-gray-500 rounded-full mr-2"></div>Archived</span>
    @endif
  </td>
  <td class="px-6 py-5 text-sm text-slate-600">{{ optional($c->updated_at)->format('M d, Y') }}</td>
  <td class="px-6 py-5">
    <div class="flex items-center justify-center space-x-2">
      <a href="{{ route('admin.courseManagement.details', $c) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 hover:bg-slate-200 hover:border-slate-300 transition-all duration-150 text-xs font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        View
      </a>
      <button onclick="openEditModal({{ $c->id }})" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-100 border border-blue-200 text-blue-700 hover:bg-blue-200 hover:border-blue-300 transition-all duration-150 text-xs font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
        </svg>
        Edit
      </button>
      <button onclick="confirmDelete({{ $c->id }}, '{{ addslashes($c->title) }}')" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-red-100 border border-red-200 text-red-700 hover:bg-red-200 hover:border-red-300 transition-all duration-150 text-xs font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
        Delete
      </button>
    </div>
  </td>
</tr>
