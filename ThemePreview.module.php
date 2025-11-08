<?php namespace ProcessWire;

/**
 * Theme Preview Module
 * 
 * Ermöglicht das Testen eines neuen Themes während das alte Theme live bleibt
 * 
 * @author Your Name
 * @version 1.0.1
 * 
 */

class ThemePreview extends WireData implements Module, ConfigurableModule {

    public static function getModuleInfo() {
        return array(
            'title' => 'Theme Preview',
            'version' => 101,
            'summary' => 'Ermöglicht das Preview und Switchen zwischen verschiedenen Themes',
            'author' => 'Your Name',
            'autoload' => true,
            'singular' => true,
            'icon' => 'paint-brush',
            'requires' => 'ProcessWire>=3.0.0'
        );
    }

    /**
     * Default configuration
     */
    public function __construct() {
        $this->set('previewThemePath', 'templates-new');
        $this->set('productionThemePath', 'templates');
        $this->set('enabledUsers', array());
        $this->set('cookieName', 'pw_theme_preview');
        $this->set('cookieDuration', 30); // Tage
    }

    /**
     * Initialize the module
     */
    public function init() {
        // Theme Switching Logik
        $this->addHookBefore('ProcessPageView::execute', $this, 'checkThemePreview');
    }

    /**
     * Ready hook - wird später aufgerufen wenn alles initialisiert ist
     */
    public function ready() {
        // Backend: Admin-Hinweis hinzufügen
        $page = $this->wire('page');
        if($page && $page->template && $page->template == 'admin') {
            $this->addHookAfter('ProcessController::execute', $this, 'addAdminButton');
        }
    }

    /**
     * Hook: Theme Preview prüfen und aktivieren
     */
    public function checkThemePreview(HookEvent $event) {
        $input = $this->wire('input');
        $user = $this->wire('user');
        $config = $this->wire('config');
        
        $usePreviewTheme = false;

        // Theme über GET-Parameter aktivieren/deaktivieren
        if($input->get('theme_preview') !== null) {
            $action = $input->get('theme_preview');
            
            if($action === 'activate') {
                setcookie($this->cookieName, '1', time() + (86400 * $this->cookieDuration), '/');
                $usePreviewTheme = true;
                $this->message("Preview Theme aktiviert!");
            } 
            else if($action === 'deactivate') {
                setcookie($this->cookieName, '', time() - 3600, '/');
                $usePreviewTheme = false;
                $this->message("Production Theme aktiviert!");
            }
        }
        // Cookie prüfen
        else if(isset($_COOKIE[$this->cookieName]) && $_COOKIE[$this->cookieName] === '1') {
            $usePreviewTheme = true;
        }

        // Sicherheit: Nur eingeloggte User mit Berechtigung
        if($usePreviewTheme) {
            if(!$user->isLoggedin()) {
                $usePreviewTheme = false;
            } else {
                // Prüfen ob User in erlaubter Liste ist (wenn definiert)
                if(count($this->enabledUsers) > 0) {
                    if(!in_array($user->id, $this->enabledUsers)) {
                        $usePreviewTheme = false;
                    }
                }
            }
        }

        // Theme-Pfade umschalten
        if($usePreviewTheme) {
            $config->urls->templates = $config->urls->site . $this->previewThemePath . '/';
            $config->paths->templates = $config->paths->site . $this->previewThemePath . '/';
        }
    }

    /**
     * Admin Button/Hinweis hinzufügen
     */
    public function addAdminButton(HookEvent $event) {
        $page = $this->wire('page');
        
        // Sicherheitscheck
        if(!$page || !$page->template) return;
        
        // Nur auf der Page-Edit-Ansicht
        if($page->process != 'ProcessPageEdit') return;
        
        $previewActive = isset($_COOKIE[$this->cookieName]) && $_COOKIE[$this->cookieName] === '1';
        
        if($previewActive) {
            $this->warning("🎨 Preview Theme ist aktiv! <a href='{$this->wire('config')->urls->root}?theme_preview=deactivate'>Deaktivieren</a>");
        }
    }

    /**
     * Modul-Konfiguration
     */
    public function getModuleConfigInputfields(array $data) {
        $inputfields = new InputfieldWrapper();
        $modules = $this->wire('modules');

        // Status & Quick Actions
        $fieldset = $modules->get('InputfieldFieldset');
        $fieldset->label = 'Status & Aktionen';
        $fieldset->icon = 'dashboard';
        
        $previewActive = isset($_COOKIE[$this->cookieName]) && $_COOKIE[$this->cookieName] === '1';
        
        $markup = $modules->get('InputfieldMarkup');
        $markup->label = 'Aktueller Status';
        
        $statusHTML = '<div style="padding: 15px; background: ' . ($previewActive ? '#d4edda' : '#f8d7da') . '; border-radius: 5px; margin-bottom: 15px;">';
        $statusHTML .= '<strong>Theme Status:</strong> ' . ($previewActive ? '🟢 Preview Theme aktiv' : '🔴 Production Theme aktiv');
        $statusHTML .= '</div>';
        
        $rootUrl = $this->wire('config')->urls->root;
        
        $statusHTML .= '<div style="margin: 20px 0;">';
        $statusHTML .= '<button type="button" id="themePreviewToggle" class="ui-button ui-state-default" style="margin-right: 10px;" data-active="' . ($previewActive ? '1' : '0') . '">';
        $statusHTML .= '<i class="fa fa-toggle-' . ($previewActive ? 'off' : 'on') . '"></i> ';
        $statusHTML .= ($previewActive ? 'Preview deaktivieren' : 'Preview aktivieren');
        $statusHTML .= '</button>';
        $statusHTML .= '<a href="' . $rootUrl . '" target="_blank" class="ui-button ui-state-default"><i class="fa fa-external-link"></i> Frontend öffnen</a>';
        $statusHTML .= '</div>';
        
        // JavaScript für Toggle-Funktionalität
        $statusHTML .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var btn = document.getElementById("themePreviewToggle");
            if(!btn) return;
            
            btn.addEventListener("click", function() {
                var isActive = btn.getAttribute("data-active") === "1";
                var action = isActive ? "deactivate" : "activate";
                var url = "' . $rootUrl . '?theme_preview=" + action;
                
                // Frontend in neuem Tab öffnen
                window.open(url, "_blank");
                
                // Button-Status sofort umschalten
                var newActive = !isActive;
                btn.setAttribute("data-active", newActive ? "1" : "0");
                
                var icon = btn.querySelector("i");
                icon.className = "fa fa-toggle-" + (newActive ? "off" : "on");
                
                btn.innerHTML = icon.outerHTML + " " + (newActive ? "Preview deaktivieren" : "Preview aktivieren");
                
                // Status-Box aktualisieren
                var statusBox = btn.closest("div").previousElementSibling;
                if(statusBox) {
                    statusBox.style.background = newActive ? "#d4edda" : "#f8d7da";
                    statusBox.innerHTML = "<strong>Theme Status:</strong> " + (newActive ? "🟢 Preview Theme aktiv" : "🔴 Production Theme aktiv");
                }
            });
        });
        </script>';
        
        $markup->value = $statusHTML;
        $fieldset->add($markup);
        $inputfields->add($fieldset);

        // Theme Pfade
        $fieldset = $modules->get('InputfieldFieldset');
        $fieldset->label = 'Theme Einstellungen';
        $fieldset->icon = 'folder';

        $field = $modules->get('InputfieldText');
        $field->name = 'productionThemePath';
        $field->label = 'Production Theme Pfad';
        $field->description = 'Relativer Pfad vom /site/ Verzeichnis';
        $field->notes = 'Standard: templates';
        $field->value = isset($data['productionThemePath']) ? $data['productionThemePath'] : 'templates';
        $field->required = true;
        $field->columnWidth = 50;
        $fieldset->add($field);

        $field = $modules->get('InputfieldText');
        $field->name = 'previewThemePath';
        $field->label = 'Preview Theme Pfad';
        $field->description = 'Relativer Pfad vom /site/ Verzeichnis';
        $field->notes = 'Standard: templates-new';
        $field->value = isset($data['previewThemePath']) ? $data['previewThemePath'] : 'templates-new';
        $field->required = true;
        $field->columnWidth = 50;
        $fieldset->add($field);

        $inputfields->add($fieldset);

        // Template Übersicht
        $fieldset = $modules->get('InputfieldFieldset');
        $fieldset->label = 'Template Übersicht';
        $fieldset->icon = 'files-o';
        $fieldset->collapsed = Inputfield::collapsedNo;

        $markup = $modules->get('InputfieldMarkup');
        $markup->label = 'Vergleich der Templates';
        $markup->description = 'Zeigt welche Template-Dateien in welchem Theme vorhanden sind';
        
        $comparisonHTML = $this->getTemplateComparison();
        $markup->value = $comparisonHTML;
        $fieldset->add($markup);

        $inputfields->add($fieldset);

        // Sicherheit & Zugriff
        $fieldset = $modules->get('InputfieldFieldset');
        $fieldset->label = 'Zugriffskontrolle';
        $fieldset->icon = 'lock';
        $fieldset->collapsed = Inputfield::collapsedYes;

        $field = $modules->get('InputfieldAsmSelect');
        $field->name = 'enabledUsers';
        $field->label = 'Berechtigte User für Preview';
        $field->description = 'Wenn leer, können alle eingeloggten User das Preview nutzen';
        $field->notes = 'Nur ausgewählte User können das Preview-Theme aktivieren und sehen';
        foreach($this->wire('users') as $u) {
            if($u->id > 0) $field->addOption($u->id, $u->name);
        }
        $field->value = isset($data['enabledUsers']) ? $data['enabledUsers'] : array();
        $fieldset->add($field);

        $field = $modules->get('InputfieldInteger');
        $field->name = 'cookieDuration';
        $field->label = 'Cookie Dauer (Tage)';
        $field->description = 'Wie lange bleibt die Preview-Aktivierung gespeichert?';
        $field->notes = 'Nach dieser Zeit muss das Preview erneut aktiviert werden';
        $field->value = isset($data['cookieDuration']) ? $data['cookieDuration'] : 30;
        $field->required = true;
        $field->min = 1;
        $field->max = 365;
        $fieldset->add($field);

        $inputfields->add($fieldset);

        return $inputfields;
    }

    /**
     * Template Vergleich generieren
     */
    protected function getTemplateComparison() {
        $config = $this->wire('config');
        
        $prodPath = $config->paths->site . $this->productionThemePath . '/';
        $prevPath = $config->paths->site . $this->previewThemePath . '/';

        if(!is_dir($prevPath)) {
            return '<div class="ui-state-error" style="padding: 15px; margin: 10px 0; border-radius: 3px;">
                    <i class="fa fa-exclamation-triangle"></i> 
                    <strong>Preview Theme Verzeichnis existiert nicht:</strong><br>
                    <code>' . $prevPath . '</code><br><br>
                    Bitte erstelle dieses Verzeichnis und kopiere deine Template-Dateien hinein.
                    </div>';
        }

        $html = '<table class="AdminDataTable AdminDataList" style="width: 100%;">';
        $html .= '<thead><tr>';
        $html .= '<th>Template Datei</th>';
        $html .= '<th style="text-align: center; width: 120px;">Production</th>';
        $html .= '<th style="text-align: center; width: 120px;">Preview</th>';
        $html .= '<th style="text-align: center; width: 150px;">Status</th>';
        $html .= '</tr></thead><tbody>';

        $prodFiles = is_dir($prodPath) ? scandir($prodPath) : array();
        $prevFiles = is_dir($prevPath) ? scandir($prevPath) : array();
        
        $allFiles = array_unique(array_merge($prodFiles, $prevFiles));
        sort($allFiles);

        $count = 0;
        foreach($allFiles as $file) {
            if($file == '.' || $file == '..' || !preg_match('/\.php$/', $file)) continue;
            
            $inProd = file_exists($prodPath . $file);
            $inPrev = file_exists($prevPath . $file);
            
            $status = '';
            $statusColor = '';
            
            if($inProd && $inPrev) {
                $status = '✓ Beide';
                $statusColor = '#d4edda';
            } else if($inPrev) {
                $status = '⚠ Nur Preview';
                $statusColor = '#fff3cd';
            } else {
                $status = '✗ Nur Production';
                $statusColor = '#f8d7da';
            }

            $html .= '<tr>';
            $html .= '<td><code>' . $this->wire('sanitizer')->entities($file) . '</code></td>';
            $html .= '<td style="text-align: center;">' . ($inProd ? '✓' : '✗') . '</td>';
            $html .= '<td style="text-align: center;">' . ($inPrev ? '✓' : '✗') . '</td>';
            $html .= '<td style="text-align: center; background: ' . $statusColor . '; font-weight: bold;">' . $status . '</td>';
            $html .= '</tr>';
            $count++;
        }

        if($count === 0) {
            $html .= '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #999;">
                      <i class="fa fa-info-circle"></i> Keine Template-Dateien gefunden
                      </td></tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}