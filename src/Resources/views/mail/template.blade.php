<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(substr(app()->getLocale(), 0, 2), ['ar','fa','ur','he']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? config('app.name', 'Athka HR') }}</title>
    @if(in_array(substr(app()->getLocale(), 0, 2), ['ar','fa','ur','he']))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    @endif
    <style>
        @if(in_array(substr(app()->getLocale(), 0, 2), ['ar','fa','ur','he']))
        body, table, td, p, div {
            font-family: 'Tajawal', 'Cairo', 'Segoe UI', Tahoma, Arial, sans-serif !important;
            direction: rtl !important;
        }
        .rtl-content {
            direction: rtl !important;
            text-align: right !important;
        }
        @else
        body, table, td, p, div {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
            direction: ltr !important;
        }
        .ltr-content {
            direction: ltr !important;
            text-align: left !important;
        }
        @endif
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 20px 0;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); max-width: 600px; width: 100%;">
                    
                    <!-- Header with Gradient -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); padding: 40px 30px; text-align: center; position: relative;">
                            <!-- Decorative Pattern -->
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
                            
                            <!-- Logo/Company Name -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="position: relative; z-index: 1;">
                                        <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: -0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            {{ config('app.name', 'Athka HR') }}
                                        </h1>
                                        <p style="margin: 8px 0 0 0; color: rgba(255, 255, 255, 0.9); font-size: 14px; font-weight: 400;">
                                            {{ tr('Human Resources Management System') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Content Body -->
                    <tr>
                        <td style="padding: 50px 40px; background-color: #ffffff;">
                            <!-- Welcome Greeting -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom: 15px;">
                                        @php
                                            // Welcome message
                                            $welcomeText = app()->getLocale() === 'ar' ? 'أهلاً وسهلاً' : 'Welcome';
                                            $arabicPattern = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';
                                            $hasArabic = preg_match($arabicPattern, $welcomeText);
                                            $welcomeDir = $hasArabic ? 'rtl' : 'ltr';
                                            $welcomeAlign = $hasArabic ? 'right' : 'left';
                                        @endphp
                                        <p style="margin: 0; color: #667eea; font-size: 20px; font-weight: 700; line-height: 1.5; text-align: {{ $welcomeAlign }}; direction: {{ $welcomeDir }};">
                                            {{ $welcomeText }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Company Name -->
                            @if(isset($companyName) && $companyName)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        @php
                                            // Detect language in company name
                                            $arabicPattern = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';
                                            $hasArabic = preg_match($arabicPattern, $companyName);
                                            $companyDir = $hasArabic ? 'rtl' : 'ltr';
                                            $companyAlign = $hasArabic ? 'right' : 'left';
                                        @endphp
                                        <p style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 600; line-height: 1.5; text-align: {{ $companyAlign }}; direction: {{ $companyDir }};">
                                            {{ $companyName }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            @endif
                            
                            <!-- Greeting -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        @php
                                            // Detect language in greeting
                                            $greetingText = tr('Dear') . ' ' . ($recipientName ?? tr('Valued Customer'));
                                            $arabicPattern = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';
                                            $hasArabic = preg_match($arabicPattern, $greetingText);
                                            $greetingDir = $hasArabic ? 'rtl' : 'ltr';
                                            $greetingAlign = $hasArabic ? 'right' : 'left';
                                        @endphp
                                        <p style="margin: 0; color: #1f2937; font-size: 16px; font-weight: 500; line-height: 1.5; text-align: {{ $greetingAlign }}; direction: {{ $greetingDir }};">
                                            {{ tr('Dear') }} {{ $recipientName ?? tr('Valued Customer') }},
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Main Content -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom: 30px;">
                                        @php
                                            // Detect language in body content
                                            $bodyText = strip_tags($body);
                                            $arabicPattern = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';
                                            $hasArabic = preg_match($arabicPattern, $bodyText);
                                            
                                            // Count Arabic vs English characters
                                            $arabicChars = preg_match_all($arabicPattern, $bodyText);
                                            $englishChars = preg_match_all('/[a-zA-Z]/', $bodyText);
                                            
                                            // Determine direction: if Arabic characters are more than 30% of total, use RTL
                                            $totalChars = $arabicChars + $englishChars;
                                            $isRtl = $totalChars > 0 && ($arabicChars / $totalChars) > 0.3;
                                            
                                            // If no clear direction, check if body starts with Arabic
                                            if (!$isRtl && !$hasArabic) {
                                                $isRtl = false;
                                            } elseif ($hasArabic && !$isRtl) {
                                                // Check first significant character
                                                $firstChar = mb_substr(trim($bodyText), 0, 1);
                                                $isRtl = preg_match($arabicPattern, $firstChar);
                                            }
                                            
                                            $textDir = $isRtl ? 'rtl' : 'ltr';
                                            $textAlign = $isRtl ? 'right' : 'left';
                                            
                                            // Process body to wrap English words/phrases in LTR spans when in RTL context
                                            // This prevents English text (like "Athka HR") from being reversed in Arabic content
                                            if ($isRtl) {
                                                // First, protect HTML tags and entities
                                                $placeholders = [];
                                                $placeholderIndex = 0;
                                                
                                                // Replace HTML tags with placeholders
                                                $bodyWithoutTags = preg_replace_callback(
                                                    '/<[^>]+>/',
                                                    function($matches) use (&$placeholders, &$placeholderIndex) {
                                                        $key = '___HTML_TAG_' . $placeholderIndex . '___';
                                                        $placeholders[$key] = $matches[0];
                                                        $placeholderIndex++;
                                                        return $key;
                                                    },
                                                    $body
                                                );
                                                
                                                // Replace HTML entities with placeholders
                                                $bodyWithoutEntities = preg_replace_callback(
                                                    '/&[a-zA-Z0-9#]+;/',
                                                    function($matches) use (&$placeholders, &$placeholderIndex) {
                                                        $key = '___HTML_ENTITY_' . $placeholderIndex . '___';
                                                        $placeholders[$key] = $matches[0];
                                                        $placeholderIndex++;
                                                        return $key;
                                                    },
                                                    $bodyWithoutTags
                                                );
                                                
                                                // Now wrap English words/phrases in LTR spans
                                                $processedBody = preg_replace_callback(
                                                    '/([a-zA-Z0-9]+(?:\s+[a-zA-Z0-9]+)*)/u',
                                                    function($matches) {
                                                        $text = trim($matches[0]);
                                                        if (strlen($text) > 0) {
                                                            return '<span dir="ltr" style="unicode-bidi: embed; direction: ltr; display: inline;">' . $text . '</span>';
                                                        }
                                                        return $matches[0];
                                                    },
                                                    $bodyWithoutEntities
                                                );
                                                
                                                // Restore HTML tags and entities
                                                foreach ($placeholders as $key => $value) {
                                                    $processedBody = str_replace($key, $value, $processedBody);
                                                }
                                            } else {
                                                $processedBody = $body;
                                            }
                                        @endphp
                                        <div style="color: #374151; font-size: 16px; line-height: 1.8; text-align: {{ $textAlign }}; direction: {{ $textDir }};">
                                            {!! $processedBody ?? $body !!}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Divider -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 20px 0;">
                                        <div style="height: 1px; background: linear-gradient({{ in_array(substr(app()->getLocale(), 0, 2), ['ar','fa','ur','he']) ? 'to left' : 'to right' }}, transparent, #e5e7eb, transparent);"></div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Call to Action (Optional) -->
                            @if(isset($actionUrl) && isset($actionText))
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-top: 10px;">
                                        <a href="{{ $actionUrl }}" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3); transition: all 0.3s ease;">
                                            {{ $actionText }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: linear-gradient(to bottom, #f9fafb 0%, #ffffff 100%); padding: 30px 40px; border-top: 1px solid #e5e7eb;">
                            <!-- Social Links (Optional) -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
                                <tr>
                                    <td align="center">
                                        <p style="margin: 0 0 12px 0; color: #6b7280; font-size: 14px; font-weight: 500;">
                                            {{ tr('Follow us') }}
                                        </p>
                                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                            <tr>
                                                <td style="padding: 0 8px;">
                                                    <a href="#" style="display: inline-block; width: 36px; height: 36px; background-color: #667eea; border-radius: 50%; text-align: center; line-height: 36px; text-decoration: none;">
                                                        <span style="color: #ffffff; font-size: 16px;">f</span>
                                                    </a>
                                                </td>
                                                <td style="padding: 0 8px;">
                                                    <a href="#" style="display: inline-block; width: 36px; height: 36px; background-color: #667eea; border-radius: 50%; text-align: center; line-height: 36px; text-decoration: none;">
                                                        <span style="color: #ffffff; font-size: 16px;">in</span>
                                                    </a>
                                                </td>
                                                <td style="padding: 0 8px;">
                                                    <a href="#" style="display: inline-block; width: 36px; height: 36px; background-color: #667eea; border-radius: 50%; text-align: center; line-height: 36px; text-decoration: none;">
                                                        <span style="color: #ffffff; font-size: 16px;">@</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Footer Text -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-top: 20px; border-top: 1px solid #e5e7eb;">
                                        <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 13px; line-height: 1.6;">
                                            {{ tr('This is an automated email from') }} <strong style="color: #667eea;">{{ config('app.name', 'Athka HR') }}</strong>
                                        </p>
                                        <p style="margin: 0 0 8px 0; color: #9ca3af; font-size: 12px;">
                                            {{ tr('If you have any questions, please contact our support team') }}
                                        </p>
                                        <p style="margin: 8px 0 0 0; color: #d1d5db; font-size: 11px;">
                                            © {{ date('Y') }} {{ config('app.name', 'Athka HR') }}. {{ tr('All rights reserved') }}.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <!-- Bottom Spacing -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 20px;">
                    <tr>
                        <td align="center" style="padding: 0 20px;">
                            <p style="margin: 0; color: #9ca3af; font-size: 11px; text-align: center;">
                                {{ tr('This email was sent to') }} {{ $recipientEmail ?? '' }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
