import { BlockStack, Card,Text, InlineStack, RadioButton, Button, InlineGrid } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useReducer, useState } from "react";
import PriceTask from "./PriceTask";
import InventoryTask from "./InventoryTask";
import TaskCondition from "./TaskCondition";
import ScheduleTaskTime from "../ScheduleTime/ScheduleTaskTime";
import CustomTaskReducer, { actionTypes, customeTaskState } from "../../reducers/CustomTaskReducer";

export default function Task() {
  const [state, dispatch] = useReducer(CustomTaskReducer, customeTaskState);
  const { t } = useTranslation();

  const handleChangeTaskType = (checked, newValue) => {
      dispatch({
        type: actionTypes.HANDLE_TASK_CHANGE,
        payload: newValue,
    });
  };

  const saveTask = () => {
    console.log("save chnages");
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
            <ScheduleTaskTime />
          </Card>
           {/* Conditions */}
           <Card>
                <BlockStack gap={200}>
                <Text variant="headingSm">Condition for {state.taskType == 'price' ? "Price": "Inventory"} Update</Text>
                <TaskCondition key={state.taskType} selectedTask={state.taskType} />
                </BlockStack>
            </Card>
        {/* What do you want to do? */}
        <Card>
            <BlockStack gap={200}>
            <Text variant="headingSm">How would you like to change your {state.taskType == 'price' ? "Price": "Inventory"}?</Text>
                {state.taskType == 'price' ? <PriceTask key="price-task" /> : <InventoryTask key="inventory-task" />}
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
