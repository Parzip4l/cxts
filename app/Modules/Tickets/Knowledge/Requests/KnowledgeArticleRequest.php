<?php

namespace App\Modules\Tickets\Knowledge\Requests;

use App\Models\KnowledgeArticle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KnowledgeArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyPermission(['ticket.view_all', 'ticket.view_department', 'ticket.assign_all', 'ticket.approve_all']) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'article_type' => ['required', Rule::in(array_keys(KnowledgeArticle::typeOptions()))],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(array_keys(KnowledgeArticle::statusOptions()))],
            'owner_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'published_at' => ['nullable', 'date'],
            'ticket_ids' => ['nullable', 'array'],
            'ticket_ids.*' => ['integer', 'distinct', Rule::exists('tickets', 'id')],
            'problem_ids' => ['nullable', 'array'],
            'problem_ids.*' => ['integer', 'distinct', Rule::exists('problems', 'id')],
        ];
    }
}
