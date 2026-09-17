---
description: Perform an impact analysis before making any fixes.
---

# Impact Analysis

Before making any fix or modifying code, you MUST always perform an impact analysis. 

1. **Check affected areas:** Analyze potential areas the fix might negatively affect or introduce side effects (e.g., regressions, broken dependencies, unexpected behavior).
2. **Identify secondary modifications:** Identify any other related areas in the codebase that MUST also be modified for the primary fix to work completely (e.g., modifying generation logic often requires modifying validation logic).
