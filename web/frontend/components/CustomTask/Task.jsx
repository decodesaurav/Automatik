import { BlockStack, Card,Text, InlineStack, RadioButton } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useState } from "react";
import PriceTask from "./PriceTask";
import InventoryTask from "./InventoryTask";
import TaskCondition from "./TaskCondition";

export default function Task() {
  const { t } = useTranslation();
  const [selected, setSelected] = useState("price");

  const handleChange = (checked, newValue) => {
    if (checked) {
      setSelected(newValue);
    }
  };

  return (
    <>
    <BlockStack gap={200}>
        <Card>
            <BlockStack gap={400}>
                <Text variant="headingMd" fontWeight="bold">Create Custom Task</Text>
                <InlineStack gap={600}>
                    <RadioButton
                    label="Inventory Task"
                    checked={selected === "inventory"}
                    id="inventory"
                    name="accounts"
                    onChange={(checked) => handleChange(checked, "inventory")}
                    />
                    <RadioButton
                    label="Price Task"
                    checked={selected === "price"}
                    id="price"
                    name="accounts"
                    onChange={(checked) => handleChange(checked, "price")}
                    />
                </InlineStack>
            </BlockStack>
        </Card>
           {/* Conditions */}
           <Card>
                <BlockStack gap={200}>
                <Text variant="headingSm">Condition for {selected == 'price' ? "Price": "Inventory"} Update</Text>
                <TaskCondition key={selected} selectedTask={selected} />
                </BlockStack>
            </Card>
        {/* What do you want to do? */}
        <Card>
            <BlockStack gap={200}>
            <Text variant="headingSm">How would you like to change your {selected == 'price' ? "Price": "Inventory"}?</Text>
                {selected == 'price' ? <PriceTask key="price-task" /> : <InventoryTask key="inventory-task" />}
            </BlockStack>
        </Card>
    </BlockStack>
    </>
  );
}
