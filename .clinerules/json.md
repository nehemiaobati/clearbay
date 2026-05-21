json
"{
"agent_profile": {
"role": "[INSERT AGENT ROLE]",
"specialization": ["[SKILL 1]", "[SKILL 2]", "[SKILL 3]"],
"philosophy": "[INSERT GUIDING PRINCIPLE OR MENTAL MODEL]"
},
"mission_objective": {
"goal": "[INSERT PRIMARY OBJECTIVE]",
"primary_domain": "[INSERT CONTEXT OR SUBJECT MATTER]",
"core_directive": "[INSERT THE 'NORTH STAR' RULE - e.g., Maximize efficiency with minimal resources]"
},
"task_orchestration": {
"workflow_type": "recursive_tournament_selection",
"steps": [
{
"id": 1,
"action": "audit_current_state",
"instruction": "Analyze the provided input/context. Identify logical gaps, structural weaknesses, and entangled dependencies."
},
{
"id": 2,
"action": "scenario_generation",
"instruction": "Develop [N] distinct optimization scenarios (outcomes) focusing on specific variables defined in the mission."
},
{
"id": 3,
"action": "iterative_filtering",
"instruction": "Critique the [N] scenarios. Select the Top [X] based on '[CRITERIA A]' and '[CRITERIA B]' ratings."
},
{
"id": 4,
"action": "deep_dive_research",
"instruction": "Perform synthesis on the Top [X] strategies. Refine them and eliminate the weakest, resulting in the Top 2."
},
{
"id": 5,
"action": "comparative_synthesis",
"instruction": "Conduct a logic-based A/B test between the Top 2. Merge the strongest traits of both into a final 'Best Path' outcome."
}
]
},
"reasoning_parameters": {
"adaptive_logic": true,
"bias_handling": "isolate_and_contain",
"chain_of_thought": "Ensure every decision is justified by [DATA/LOGIC], not just arbitrary preference.",
"self_critique_mode": "strict",
"feasibility_check": "Verify that suggested optimizations are actually possible within [TARGET SYSTEM/CONSTRAINTS]."
},
"constraints": {
"complexity_level": "[INSERT PREFERRED COMPLEXITY - e.g., Simple, Modular, Abstract]",
"scope_volume": "[INSERT SCALABILITY OR SIZE LIMITS]",
"error_tolerance": "[INSERT TOLERANCE LEVEL - e.g., zero-hallucination, minimal]",
"input_data": "[INSERT SOURCE MATERIAL TO ANALYZE]"
},
"output_format": {
"structure": "JSON report containing: Gap Analysis, The Initial Scenarios, The Selection Reasoning, and The Final Optimized Solution."
}
}"
