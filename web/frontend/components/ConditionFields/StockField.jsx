import { InlineStack, RadioButton, Select, TextField,Text, BlockStack, InlineError } from "@shopify/polaris";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useState } from "react";

export default function StockField({state,dispatch}) {
  const { t } = useTranslation();
  const [method, setMethod] = useState('increase');
  const [value, setValue] = useState(0);

  const handleMethodChange = (value) => {
    dispatch({
      type: 'HANDLE_CONDITION_CHANGE',
      payload: { field: 'stock', data: { method: value } },
    });
  };

  const handleValueChange = (newValue) => {
    dispatch({
      type: 'HANDLE_CONDITION_CHANGE',
      payload: { field: 'stock', data: { value: newValue } },
    });
  };
 
  const condition = state.conditions.find(cond => cond.field === 'stock') || {};
  const errorData = state.errorData?.conditions?.['stock'] || {};

  return (
    <>
        <BlockStack gap={100}>
            <Text>Where stock is</Text>
            <InlineStack gap={200}>
              <BlockStack>
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
                  {errorData?.method && (
                    <InlineError message={errorData.method} fieldID="stockMethod" />
                  )}
                </BlockStack>
                <BlockStack>
                    <TextField
                        type="number"
                        value={condition?.value ?? 0}
                        onChange={handleValueChange}
                    />
                    {errorData?.value && (
                        <InlineError message={errorData.value} fieldID="stockValue" />
                    )}
                </BlockStack>
                </InlineStack>
        </BlockStack>
    </>
  );

}
