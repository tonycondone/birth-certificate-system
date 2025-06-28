<?php

namespace App\Services;

class AccessibilityService
{
    private array $ariaLabels = [];
    private array $skipLinks = [];
    private array $landmarks = [];

    /**
     * Generate skip navigation links
     */
    public function generateSkipLinks(): string
    {
        $html = '<div class="skip-links">';
        foreach ($this->skipLinks as $target => $label) {
            $html .= sprintf(
                '<a href="#%s" class="skip-link">%s</a>',
                htmlspecialchars($target),
                htmlspecialchars($label)
            );
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Add a skip navigation link
     */
    public function addSkipLink(string $target, string $label): self
    {
        $this->skipLinks[$target] = $label;
        return $this;
    }

    /**
     * Generate ARIA landmark roles
     */
    public function generateLandmarks(): array
    {
        return $this->landmarks;
    }

    /**
     * Add an ARIA landmark
     */
    public function addLandmark(string $role, string $label, array $attributes = []): self
    {
        $this->landmarks[] = [
            'role' => $role,
            'label' => $label,
            'attributes' => $attributes
        ];
        return $this;
    }

    /**
     * Generate ARIA labels
     */
    public function getAriaLabels(): array
    {
        return $this->ariaLabels;
    }

    /**
     * Add an ARIA label
     */
    public function addAriaLabel(string $elementId, string $label): self
    {
        $this->ariaLabels[$elementId] = $label;
        return $this;
    }

    /**
     * Generate accessible form field
     */
    public function generateFormField(
        string $type,
        string $name,
        string $label,
        array $attributes = [],
        string $error = ''
    ): string {
        $id = $attributes['id'] ?? $name;
        $required = !empty($attributes['required']);
        $value = $attributes['value'] ?? '';
        $placeholder = $attributes['placeholder'] ?? '';
        
        $html = '<div class="form-group">';
        
        // Label
        $html .= sprintf(
            '<label for="%s" class="form-label">%s%s</label>',
            htmlspecialchars($id),
            htmlspecialchars($label),
            $required ? ' <span class="required" aria-hidden="true">*</span>' : ''
        );

        // Input field
        $attributesStr = '';
        foreach ($attributes as $key => $val) {
            if ($key !== 'value' && $key !== 'placeholder') {
                $attributesStr .= sprintf(' %s="%s"', $key, htmlspecialchars($val));
            }
        }

        $html .= sprintf(
            '<input type="%s" name="%s" id="%s" value="%s" placeholder="%s"%s%s%s class="form-control%s" aria-describedby="%s-help%s">',
            $type,
            htmlspecialchars($name),
            htmlspecialchars($id),
            htmlspecialchars($value),
            htmlspecialchars($placeholder),
            $attributesStr,
            $required ? ' required' : '',
            $error ? ' aria-invalid="true"' : '',
            $error ? ' is-invalid' : '',
            htmlspecialchars($id),
            $error ? " {$id}-error" : ''
        );

        // Help text
        if (!empty($attributes['help'])) {
            $html .= sprintf(
                '<div id="%s-help" class="form-text">%s</div>',
                htmlspecialchars($id),
                htmlspecialchars($attributes['help'])
            );
        }

        // Error message
        if ($error) {
            $html .= sprintf(
                '<div id="%s-error" class="invalid-feedback">%s</div>',
                htmlspecialchars($id),
                htmlspecialchars($error)
            );
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Generate accessible button
     */
    public function generateButton(
        string $text,
        array $attributes = [],
        bool $isLoading = false
    ): string {
        $type = $attributes['type'] ?? 'button';
        $class = $attributes['class'] ?? 'btn btn-primary';
        $disabled = !empty($attributes['disabled']);
        
        $attributesStr = '';
        foreach ($attributes as $key => $val) {
            if (!in_array($key, ['type', 'class'])) {
                $attributesStr .= sprintf(' %s="%s"', $key, htmlspecialchars($val));
            }
        }

        return sprintf(
            '<button type="%s" class="%s"%s%s>
                %s
                %s
            </button>',
            $type,
            $class,
            $disabled ? ' disabled' : '',
            $attributesStr,
            $isLoading ? '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' : '',
            htmlspecialchars($text)
        );
    }

    /**
     * Generate accessible alert
     */
    public function generateAlert(
        string $message,
        string $type = 'info',
        bool $dismissible = true
    ): string {
        $iconMap = [
            'success' => 'check-circle',
            'danger' => 'exclamation-circle',
            'warning' => 'exclamation-triangle',
            'info' => 'info-circle'
        ];

        $icon = $iconMap[$type] ?? 'info-circle';

        $html = sprintf(
            '<div class="alert alert-%s%s" role="alert"%s>',
            htmlspecialchars($type),
            $dismissible ? ' alert-dismissible fade show' : '',
            $dismissible ? ' aria-live="polite"' : ''
        );

        $html .= sprintf(
            '<i class="fas fa-%s me-2" aria-hidden="true"></i>',
            htmlspecialchars($icon)
        );

        $html .= htmlspecialchars($message);

        if ($dismissible) {
            $html .= '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>';
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Generate accessible modal
     */
    public function generateModal(
        string $id,
        string $title,
        string $content,
        array $buttons = []
    ): string {
        $html = sprintf(
            '<div class="modal fade" id="%s" tabindex="-1" role="dialog" aria-labelledby="%s-title" aria-hidden="true">',
            htmlspecialchars($id),
            htmlspecialchars($id)
        );

        $html .= '<div class="modal-dialog" role="document">';
        $html .= '<div class="modal-content">';

        // Header
        $html .= '<div class="modal-header">';
        $html .= sprintf(
            '<h5 class="modal-title" id="%s-title">%s</h5>',
            htmlspecialchars($id),
            htmlspecialchars($title)
        );
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        $html .= '</div>';

        // Body
        $html .= sprintf(
            '<div class="modal-body">%s</div>',
            $content
        );

        // Footer with buttons
        if (!empty($buttons)) {
            $html .= '<div class="modal-footer">';
            foreach ($buttons as $button) {
                $html .= $this->generateButton(
                    $button['text'],
                    $button['attributes'] ?? []
                );
            }
            $html .= '</div>';
        }

        $html .= '</div></div></div>';
        return $html;
    }

    /**
     * Generate accessible table
     */
    public function generateTable(
        array $headers,
        array $data,
        array $options = []
    ): string {
        $caption = $options['caption'] ?? '';
        $responsive = $options['responsive'] ?? true;
        $striped = $options['striped'] ?? true;

        $html = $responsive ? '<div class="table-responsive">' : '';
        
        $html .= sprintf(
            '<table class="table%s" role="grid">',
            $striped ? ' table-striped' : ''
        );

        if ($caption) {
            $html .= sprintf('<caption>%s</caption>', htmlspecialchars($caption));
        }

        // Headers
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= sprintf(
                '<th scope="col">%s</th>',
                htmlspecialchars($header)
            );
        }
        $html .= '</tr></thead>';

        // Body
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= sprintf('<td>%s</td>', htmlspecialchars($cell));
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';
        $html .= $responsive ? '</div>' : '';

        return $html;
    }
}