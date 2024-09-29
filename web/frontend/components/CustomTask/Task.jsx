import { BlockStack, Card,Text, InlineStack, RadioButton, Button, InlineGrid } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useReducer, useState } from "react";
import PriceTask from "./PriceTask";
import InventoryTask from "./InventoryTask";
import TaskCondition from "./TaskCondition";
import ScheduleTaskTime from "../ScheduleTime/ScheduleTaskTime";
import CustomTaskReducer, { actionTypes, customeTaskState } from "../../reducers/CustomTaskReducer";
import { useAppBridge, useAuthenticatedFetch } from "@shopify/app-bridge-react";
import { Redirect } from "@shopify/app-bridge/actions";

export default function Task() {
  const [state, dispatch] = useReducer(CustomTaskReducer, customeTaskState);
  const { t } = useTranslation();
  const fetch = useAuthenticatedFetch();
  const app = useAppBridge();

  const handleChangeTaskType = (checked, newValue) => {
      dispatch({
        type: actionTypes.HANDLE_TASK_CHANGE,
        payload: newValue,
    });
  };

  const validateFields = () => {
    const errors = { conditions: [] };
    if (!state.scheduleData.scheduled_at_date) {
        errors.scheduled_at_date = "Schedule date is required.";
    }
    if (!state.scheduleData.schedule_at_time) {
        errors.schedule_at_time = "Schedule time is required.";
    }
    if (state.revertSchedule && !state.scheduleData.revert_at_date) {
        errors.revert_at_date = "Revert date is required.";
    }
    if (state.revertSchedule && !state.scheduleData.revert_at_time) {
        errors.revert_at_time = "Revert time is required.";
    }
    if (!state.scheduleOnlyOneTime && !state.scheduleData.reschedule_frequency) {
        errors.reschedule_frequency = "Reschedule frequency is required.";
    }
    if (!state.conditionsOptions || state.conditionsOptions.length <= 0 ) {
      errors.conditionsOptions = "Atleast one condition field is required.";
    }

    if (!state.taskName || state.taskName=="") {
      errors.task_name = "Task Name is required.";
    }

    //Validate Adjustment
    if (!state.adjustment.method ) {
      errors.adjustment_method = "Adjustment method is required.";
    }
    if (!state.adjustment.value ) {
      errors.adjustment_value = "Adjustment value is required.";
    }
    if (!state.adjustment.adjustmentType ) {
      errors.adjustment_type = "Adjustment Type is required.";
    }

    // Validate existing conditions
    if (Array.isArray(state.conditions) && state.conditions.length > 0) {
      state.conditions.forEach((condition) => {
        const { field } = condition;
  
        if (!state.conditionsOptions.includes(field)) {
          return; // Skip if the field is not in conditionsOptions
        }
  
        // Initialize error object for the condition
        if (!errors.conditions[field]) {
          errors.conditions[field] = {};
        }
  
        // Validate condition properties
        if (!field) {
          errors.conditions[field].field = "Condition field is required.";
        }
  
        if (!condition.method) {
          errors.conditions[field].method = "Method is required.";
        }
  
        if (condition.value == null || condition.value <= 0) {
          errors.conditions[field].value = "Value must be greater than zero.";
        }
      });
    }
  
    // If conditionsOptions exist but conditions are empty, validate each option
    if (Array.isArray(state.conditionsOptions) && state.conditionsOptions.length > 0 && state.conditions.length === 0) {
      state.conditionsOptions.forEach((option) => {
        errors.conditions[option] = {
          field: `${option.charAt(0).toUpperCase() + option.slice(1)} condition is required.`,
          method: "Method is required.",
          value: "Value must be greater than zero."
        };
      });
    }
  
    return errors;
  };

const handleValidation = () => {
    const errors = validateFields();
    dispatch({ type: actionTypes.SET_ERRORS_DATA, payload: errors});
    return Object.keys(errors).length === 0;
};

  const saveTask = () => {
      handleValidation();
      const formData = {
        task_type: state.taskType,
        schedule_time: `${state.scheduleData.scheduled_at_date} ${state.scheduleData.schedule_at_time}`,
        revert_time: state.revertSchedule
            ? `${state.scheduleData.revert_at_date} ${state.scheduleData.revert_at_time}`
            : '',
        frequency: state.scheduleData.reschedule_frequency || '',
        adjustment: {
            value: state.adjustment.value,
            adjustment_type: state.adjustment.adjustmentType,
            method: state.adjustment.method,
        },
        conditions: [],
    };

    // Add conditions data to formData object
    state.conditions.forEach((condition) => {
        formData.conditions.push({
            field: condition.field,
            value: condition.value,
            method: condition.method,
        });
    });

    console.log(formData, "formData");

    const response = fetch("/api/tasks", {
          method: "POST",
          body: JSON.stringify(formData),
          headers: {
              "Content-Type": "application/json",
          }
      })
      .then((response) => {
          if (!response.ok) {
              return "not ok";
          }
          return response.json();

      }).then((response) => {
        if(response.success){
          const redirect = Redirect.create(app);
          redirect.dispatch(
            Redirect.Action.APP,
            "/tasklist"
          );
        }
      })
      .catch((error) => {
          console.log(error);
      });

  }

  return (
    <>
    <BlockStack gap={200}>
        <Card>
            <BlockStack gap={400}>
                <Text variant="headingMd" fontWeight="bold">Create Custom Task</Text>
                <InlineStack gap={600}>
                    <RadioButton
                    label="Inventory Task"
                    checked={state.taskType === "inventory"}
                    id="inventory"
                    name="accounts"
                    onChange={(checked) => handleChangeTaskType(checked, "inventory")}
                    />
                    <RadioButton
                    label="Price Task"
                    checked={state.taskType === "price"}
                    id="price"
                    name="accounts"
                    onChange={(checked) => handleChangeTaskType(checked, "price")}
                    />
                </InlineStack>
            </BlockStack>
        </Card>
          <Card gap={200}>
            <Text variant="headingSm">Schedule Task (Start/End and frequency of Task)</Text>
            <ScheduleTaskTime stateData={state} dispatch={dispatch} actionTypes={actionTypes} errorData={state.errorData}/>
          </Card>
           {/* Conditions */}
           <Card>
                <BlockStack gap={200}>
                <Text variant="headingSm">Condition for {state.taskType == 'price' ? "Price": "Inventory"} Update</Text>
                <TaskCondition
                     key={state.taskType}
                    selectedTask={state.taskType}
                    stateData={state}
                    dispatch={dispatch}
                    actionTypes={actionTypes}
                    errorData={state.errorData} />
                </BlockStack>
            </Card>
        {/* What do you want to do? */}
        <Card>
            <BlockStack gap={200}>
            <Text variant="headingSm">How would you like to change your {state.taskType == 'price' ? "Price": "Inventory"}?</Text>
                {state.taskType == 'price' ? <PriceTask stateData={state} dispatch={dispatch} actionTypes={actionTypes} errorData={state.errorData} key="price-task" /> : 
                <InventoryTask key="inventory-task" stateData={state} dispatch={dispatch} actionTypes={actionTypes} errorData={state.errorData} />}
            </BlockStack>
        </Card>

        <InlineGrid columns="1fr auto">
            <Text as="h2" variant="headingLg"></Text>
            <Button variant="primary" onClick={saveTask}>
                Save Task
            </Button>
        </InlineGrid>
    </BlockStack>
    </>
  );
}
