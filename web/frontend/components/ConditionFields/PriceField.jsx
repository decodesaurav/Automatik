  import { InlineStack, Select, TextField, Text, BlockStack, InlineError } from "@shopify/polaris";
  import { useTranslation } from "react-i18next";
  import { useCallback } from "react";

  export default function PriceField({ state, dispatch }) {
    const { t } = useTranslation();

    const condition = state.conditions.find(cond => cond.field === 'price') || {};
    const errorData = state.errorData?.conditions?.['price'] || {};
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

    return (
      <>
        <BlockStack gap={100}>
          <Text>Where Price is</Text>
          <InlineStack gap={200}>
            <BlockStack>
              <Select
                options={[
                  { label: "Greater than", value: "greater_than" },
                  { label: "Less than", value: "less_than" },
                ]}
                onChange={handleMethodChange}
                value={condition?.method || 'greater_than'}
              />
              {errorData?.method && (
                <InlineError message={errorData.method} fieldID="priceMethod" />
              )}
            </BlockStack>
            <BlockStack>
              <TextField
                type="number"
                value={condition?.value ?? 0}
                onChange={handleValueChange}
              />
              {errorData?.value && (
                <InlineError message={errorData.value} fieldID="priceValue" />
              )}
            </BlockStack>
          </InlineStack>
        </BlockStack>
      </>
    );
  }
