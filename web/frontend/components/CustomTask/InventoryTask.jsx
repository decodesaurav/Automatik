import { InlineStack, RadioButton, Select, TextField } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useState } from "react";

export default function InventoryTask() {
  const { t } = useTranslation();
  const [method, setMethod] = useState('increase');
  const [value, setValue] = useState(0);
  const [adjustmentType, setAdjustmentType] = useState('amount');


    const handleAdjustmentChange = useCallback((value) => setMethod(value), []);
    const handleAdjustmentTypeChange = useCallback(
        (value) => setAdjustmentType(value),
        []
    );
    const handleValueChange = useCallback((newValue) => setValue(newValue), []);

  return (
    <>
        
        <InlineStack gap={200}>
                <Select
                    options={[
                        {
                            label: "Increase stock by",
                            value: "increase",
                        },
                        {
                            label: "Decrease stock by",
                            value: "decrease",
                        },
                    ]}
                    onChange={handleAdjustmentChange}
                    value={method}
                />
                <TextField
                    type="number"
                    min={0}
                    value={value}
                    onChange={handleValueChange}
                />
            </InlineStack>
    </>
  );

}
