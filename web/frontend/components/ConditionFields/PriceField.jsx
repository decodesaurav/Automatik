import { InlineStack, RadioButton, Select, TextField,Text, BlockStack } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useState } from "react";

export default function PriceField({state,dispatch}) {
  const { t } = useTranslation();

  const handleMethodChange = (value) => {
    dispatch({
      type: 'HANDLE_CONDITION_CHANGE',
      payload: { field: 'price', data: { method: value } },
    });
  };

  const handleValueChange = (newValue) => {
    dispatch({
      type: 'HANDLE_CONDITION_CHANGE',
      payload: { field: 'price', data: { value: newValue } },
    });
  };
  const condition = state.conditions.find(cond => cond.field === 'price') || {};
  return (
    <>
        <BlockStack gap={100}>
            <Text>Where Price is</Text>
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
                        onChange={handleMethodChange}
                        value={condition?.method || 'greater_than'}
                    />
                    <TextField
                        type="number"
                        value={condition?.value ?? 0}
                        onChange={handleValueChange}
                    />
                </InlineStack>
        </BlockStack>
    </>
  );

}
