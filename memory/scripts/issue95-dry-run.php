<?php
require_once '/home/claude/issue95/class-gonorth-corrections.php';
GoNorth_Corrections::register_type();
$type = get_post_type_object('gn_correction');
$checks = array(
  'registered' => (bool) $type,
  'public' => $type ? $type->public : null,
  'rest' => $type ? $type->show_in_rest : null,
  'source_56_impacts' => count(GoNorth_Corrections::impact_candidates(56)),
  'source_423_impacts' => count(GoNorth_Corrections::impact_candidates(423)),
);
echo wp_json_encode($checks, JSON_UNESCAPED_UNICODE) . PHP_EOL;