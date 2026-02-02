"""Template service for managing model templates."""

from __future__ import annotations

import logging
from typing import Any, Dict
from uuid import UUID

from sqlalchemy.orm import Session

from app.models.model_template import ModelTemplate
from app.models.variable import Variable, VariableCategory, ValueType

logger = logging.getLogger(__name__)


class TemplateService:
    """Manages model templates"""

    BUILTIN_TEMPLATES: Dict[str, Dict[str, Any]] = {
        "financial_projection": {
            "name": "Financial Projection (5 Years)",
            "category": "finance",
            "description": "Revenue, costs, and profit projection model",
            "variables": [
                {
                    "key": "initial_revenue",
                    "label": "Year 1 Revenue",
                    "category": "input",
                    "value_type": "number",
                    "raw_value": "1000000",
                    "unit": "$",
                    "description": "Year 1 revenue",
                    "display_order": 1,
                },
                {
                    "key": "growth_rate",
                    "label": "Annual Growth Rate",
                    "category": "assumption",
                    "value_type": "number",
                    "raw_value": "15",
                    "unit": "%",
                    "description": "Annual growth rate",
                    "display_order": 2,
                },
                {
                    "key": "cost_percentage",
                    "label": "Cost Percentage",
                    "category": "assumption",
                    "value_type": "number",
                    "raw_value": "60",
                    "unit": "%",
                    "description": "Costs as percentage of revenue",
                    "display_order": 3,
                },
                {
                    "key": "revenue_year2",
                    "label": "Year 2 Revenue",
                    "category": "calculation",
                    "value_type": "formula",
                    "formula": "{{initial_revenue}} * (1 + {{growth_rate}} / 100)",
                    "unit": "$",
                    "description": "Projected Year 2 revenue",
                    "display_order": 4,
                },
                {
                    "key": "revenue_year3",
                    "label": "Year 3 Revenue",
                    "category": "calculation",
                    "value_type": "formula",
                    "formula": "{{revenue_year2}} * (1 + {{growth_rate}} / 100)",
                    "unit": "$",
                    "description": "Projected Year 3 revenue",
                    "display_order": 5,
                },
                {
                    "key": "costs_year1",
                    "label": "Year 1 Costs",
                    "category": "calculation",
                    "value_type": "formula",
                    "formula": "{{initial_revenue}} * {{cost_percentage}} / 100",
                    "unit": "$",
                    "description": "Year 1 costs",
                    "display_order": 6,
                },
                {
                    "key": "profit_year1",
                    "label": "Year 1 Profit",
                    "category": "output",
                    "value_type": "formula",
                    "formula": "{{initial_revenue}} - {{costs_year1}}",
                    "unit": "$",
                    "description": "Year 1 profit",
                    "display_order": 7,
                },
            ],
        },
        "market_sizing": {
            "name": "Market Sizing (TAM/SAM/SOM)",
            "category": "strategy",
            "description": "Total Addressable Market, Serviceable Addressable Market, Serviceable Obtainable Market",
            "variables": [
                {
                    "key": "total_population",
                    "label": "Total Population",
                    "category": "input",
                    "value_type": "number",
                    "raw_value": "1000000000",
                    "unit": "people",
                    "description": "Total target population",
                    "display_order": 1,
                },
                {
                    "key": "addressable_percentage",
                    "label": "Addressable %",
                    "category": "assumption",
                    "value_type": "number",
                    "raw_value": "20",
                    "unit": "%",
                    "description": "Percentage of population that could use the product",
                    "display_order": 2,
                },
                {
                    "key": "tam",
                    "label": "TAM (Total Addressable Market)",
                    "category": "calculation",
                    "value_type": "formula",
                    "formula": "{{total_population}} * {{addressable_percentage}} / 100",
                    "unit": "people",
                    "description": "Total Addressable Market",
                    "display_order": 3,
                },
            ],
        },
        "breakeven_analysis": {
            "name": "Break-even Analysis",
            "category": "finance",
            "description": "Calculate break-even point for a business",
            "variables": [
                {
                    "key": "fixed_costs",
                    "label": "Fixed Costs",
                    "category": "input",
                    "value_type": "number",
                    "raw_value": "50000",
                    "unit": "$",
                    "description": "Monthly fixed costs",
                    "display_order": 1,
                },
                {
                    "key": "price_per_unit",
                    "label": "Price per Unit",
                    "category": "input",
                    "value_type": "number",
                    "raw_value": "100",
                    "unit": "$",
                    "description": "Price per unit sold",
                    "display_order": 2,
                },
                {
                    "key": "variable_cost_per_unit",
                    "label": "Variable Cost per Unit",
                    "category": "input",
                    "value_type": "number",
                    "raw_value": "30",
                    "unit": "$",
                    "description": "Variable cost per unit",
                    "display_order": 3,
                },
                {
                    "key": "contribution_margin",
                    "label": "Contribution Margin",
                    "category": "calculation",
                    "value_type": "formula",
                    "formula": "{{price_per_unit}} - {{variable_cost_per_unit}}",
                    "unit": "$",
                    "description": "Contribution margin per unit",
                    "display_order": 4,
                },
                {
                    "key": "breakeven_units",
                    "label": "Break-even Units",
                    "category": "output",
                    "value_type": "formula",
                    "formula": "{{fixed_costs}} / {{contribution_margin}}",
                    "unit": "units",
                    "description": "Number of units needed to break even",
                    "display_order": 5,
                },
            ],
        },
    }

    async def apply_template(
        self, db: Session, project_id: UUID, template_id: str
    ) -> Dict[str, Any]:
        """Apply template to project - creates all variables"""
        if template_id not in self.BUILTIN_TEMPLATES:
            # Try to load from database
            template = (
                db.query(ModelTemplate)
                .filter(ModelTemplate.id == UUID(template_id))
                .first()
            )
            if not template:
                raise ValueError(f"Template not found: {template_id}")
            template_data = {
                "name": template.name,
                "variables": template.variables_schema or [],
            }
        else:
            template_data = self.BUILTIN_TEMPLATES[template_id]

        created_variables = []
        variable_map: Dict[str, UUID] = {}  # Map key -> id for dependencies

        # Create variables in order
        for var_def in template_data["variables"]:
            var = Variable(
                project_id=project_id,
                key=var_def["key"],
                label=var_def.get("label", var_def["key"]),
                value_type=ValueType(var_def["value_type"]),
                category=VariableCategory(var_def["category"]),
                raw_value=var_def.get("raw_value", ""),
                formula=var_def.get("formula"),
                display_order=var_def.get("display_order", 0),
                description=var_def.get("description"),
                unit=var_def.get("unit"),
            )

            db.add(var)
            db.flush()  # Get the ID
            variable_map[var_def["key"]] = var.id
            created_variables.append(var)

        # Update depends_on for formula variables
        for i, var_def in enumerate(template_data["variables"]):
            if var_def.get("formula"):
                depends_on = []
                # Extract dependencies from formula
                from app.services.formula_parser import FormulaParser

                parser = FormulaParser()
                parse_result = parser.parse_formula(var_def["formula"])
                if parse_result["valid"]:
                    for dep_ref in parse_result["variables"]:
                        if dep_ref in variable_map:
                            depends_on.append(variable_map[dep_ref])

                created_variables[i].depends_on = depends_on if depends_on else None

        db.commit()

        # Calculate formula variables
        from app.services.loom_engine import LoomEngine

        engine = LoomEngine(db)
        await engine.calculate_all(project_id)

        logger.info(
            "Template applied template_id=%s project_id=%s variables=%d",
            template_id,
            project_id,
            len(created_variables),
        )

        return {
            "template_id": template_id,
            "template_name": template_data["name"],
            "variables_created": len(created_variables),
            "variables": [v.key for v in created_variables],
        }

    async def create_template_from_project(
        self,
        db: Session,
        project_id: UUID,
        template_name: str,
        is_public: bool = False,
        created_by_id: int | None = None,
    ) -> ModelTemplate:
        """Save current project as reusable template"""
        # Get all variables from project
        variables = (
            db.query(Variable)
            .filter(Variable.project_id == project_id)
            .order_by(Variable.display_order)
            .all()
        )

        # Serialize variables
        variables_schema = []
        for var in variables:
            var_dict = {
                "key": var.key,
                "label": var.label,
                "category": var.category.value,
                "value_type": var.value_type.value,
                "raw_value": var.raw_value,
                "formula": var.formula,
                "display_order": var.display_order,
                "description": var.description,
                "unit": var.unit,
            }
            variables_schema.append(var_dict)

        # Create template
        template = ModelTemplate(
            name=template_name,
            description=f"Template created from project {project_id}",
            variables_schema=variables_schema,
            is_public=is_public,
            created_by_id=created_by_id,
        )

        db.add(template)
        db.commit()
        db.refresh(template)

        logger.info("Template created from project id=%s template_id=%s", project_id, template.id)

        return template

