import { InlineStack, RadioButton, Select, TextField,Text, BlockStack } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useState } from "react";

export default function StockField() {
  const { t } = useTranslation();
  const [method, setMethod] = useState('increase');
  const [value, setValue] = useState(0);

  return (
    <>
        <BlockStack gap={100}>
            <Text>Where stock is</Text>
            <InlineStack gap={200}>
                    <Select
                        options={[
                            {
                                label: "Greater than",
                                value: "greater_than",
                            },
                            {
                                label: "Less than",
                                value: "less_than",
                            },
                        ]}
                        // onChange={handleAdjustmentChange}
                        value={method}
                    />
                    <TextField
                        type="number"
                        min={0}
                        value={value}
                        // onChange={handleValueChange}
                    />
                </InlineStack>
        </BlockStack>
    </>
  );

}
