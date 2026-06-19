<?php
/**
 * Standard visible action row for summary / report filter forms (FR, DR/FR, MM, BK).
 *
 * @param string $submitName  GET/POST parameter name for submit (e.g. print_summary)
 * @param string $submitLabel Button label (e.g. PRINT SUMMARY)
 */
function fr_summary_form_actions($submitName, $submitLabel)
{
    $submitName = htmlspecialchars($submitName, ENT_QUOTES, 'UTF-8');
    $submitLabel = htmlspecialchars($submitLabel, ENT_QUOTES, 'UTF-8');
    echo '<div class="col-md-12 col-sm-12 col-xs-12 fr-summary-form-actions" style="margin-top: 1.5em; padding-bottom: 2em;">';
    echo '<input class="btn btn-primary" type="submit" name="' . $submitName . '" value="' . $submitLabel . '" />';
    echo '<input class="btn btn-danger" type="reset" value="CLEAR FORM" />';
    echo '</div>';
}

function fr_summary_content_open()
{
    echo '<div class="col-md-9 background_image_ycdo" style="min-height: 450px; padding: 20px;">';
}

/** @deprecated Prefer direct form action to print script; use fr_summary_http_redirect only when needed */
function fr_summary_print_redirect($printUrl, $returnPage, $loadingLabel = 'Opening report')
{
    fr_summary_http_redirect($printUrl);
}

/** Send browser straight to the report (no JS popup — avoids stuck “loading report” pages). */
function fr_summary_http_redirect(string $printUrl): void
{
    header('Location: ' . $printUrl);
    exit;
}

/** Opening tag for forms that open the report in a new tab without a redirect page. */
function fr_report_form_open(string $printScript, string $returnPage = ''): void
{
    $action = htmlspecialchars($printScript, ENT_QUOTES, 'UTF-8');
    echo '<form action="' . $action . '" method="GET" target="_blank" class="container-fluid"';
    if ($returnPage !== '') {
        echo ' data-return="' . htmlspecialchars($returnPage, ENT_QUOTES, 'UTF-8') . '"';
    }
    echo '>';
}
