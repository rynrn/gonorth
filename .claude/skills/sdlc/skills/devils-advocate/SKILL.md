---
name: devils-advocate
description: Challenge a diagnosis and proposed root-cause fix using independent evidence.
---

# Manual diagnosis challenge

Gather symptom, cause, evidence, failing repro/output, proposed fix if any, and rootLine from the conversation; ask for missing facts. Read git log -n 20 --oneline -- <root file> using an argument array after validating the root file. Dispatch the no-shell sdlc:devils-advocate agent using CHALLENGE schema in ../../workflows/common.js, with only those inputs plus history and a fenced ticket; never dispatcher reasoning. Relay verdicts. If not proven, run its distinguishing observation through investigator/checker before applying a fix. If no proposed fix, questions 1–7 only. Null or malformed output is a failed gate.
